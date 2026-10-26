<?php

namespace App\Actions\Penarikan;

use App\Helpers\ActivityLogger;
use App\Models\KolektorNasabah;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Pembatalan penarikan oleh nasabah (keputusan D18).
 *
 * Aturan, semuanya di dalam satu transaksi dengan `lockForUpdate` pada baris
 * penarikan LALU `SaldoProduk` (urutan kunci yang sama dengan approve):
 *
 * - Hanya pemilik (`nasabah_id = pelaku->id`) dengan status `pending`
 *   (termasuk sudah disetujui fase-1 approval ganda) atau `approved`;
 *   selain itu `DomainException` dan tidak ada perubahan.
 * - `pending`: hanya status berubah — saldo belum dipotong.
 * - `approved`: saldo dikembalikan tepat `nominal_diminta` (approve memotong
 *   `nominal_diminta`) di transaksi yang sama. Kolaborator yang sedang
 *   memproses (approve/selesai/verifikasi) gagal dengan aman karena mereka
 *   memeriksa status di dalam transaksi berlock.
 *
 * Log `batalkan_penarikan` + notifikasi in-app admin (dan kolektor
 * penanggung jawab bila pengambilan di rumah kolektor) dikirim pasca-commit.
 */
class BatalkanPenarikanAction
{
    /**
     * @throws \DomainException bila bukan pemilik atau status di luar {pending, approved}
     */
    public function execute(TransaksiPenarikan|int $penarikan, User $pelaku, ?string $alasan = null): TransaksiPenarikan
    {
        $penarikanId = $penarikan instanceof TransaksiPenarikan ? $penarikan->id : $penarikan;

        /** @var array{penarikan: TransaksiPenarikan, status_sebelumnya: string, saldo_dikembalikan: bool} $hasil */
        $hasil = DB::transaction(function () use ($penarikanId, $pelaku, $alasan): array {
            $baris = TransaksiPenarikan::whereKey($penarikanId)->lockForUpdate()->first();

            if ($baris === null
                || (int) $baris->nasabah_id !== (int) $pelaku->id
                || ! in_array($baris->status, ['pending', 'approved'], true)) {
                throw new \DomainException('Pengajuan penarikan tidak dapat dibatalkan.');
            }

            $statusSebelumnya = (string) $baris->status;
            $saldoDikembalikan = $statusSebelumnya === 'approved';

            if ($saldoDikembalikan) {
                $saldo = SaldoProduk::where('nasabah_id', $baris->nasabah_id)
                    ->where('produk_id', $baris->produk_id)
                    ->lockForUpdate()
                    ->first();

                if ($saldo === null) {
                    throw new \DomainException('Pengajuan penarikan tidak dapat dibatalkan.');
                }

                $saldo->increment('saldo', $baris->nominal_diminta);
            }

            $baris->update([
                'status' => 'dibatalkan',
                'dibatalkan_oleh' => $pelaku->id,
                'alasan_batal' => $alasan !== null && $alasan !== '' ? $alasan : null,
                'waktu_dibatalkan' => now(),
                'terkunci_hingga' => null,
            ]);

            return [
                'penarikan' => $baris,
                'status_sebelumnya' => $statusSebelumnya,
                'saldo_dikembalikan' => $saldoDikembalikan,
            ];
        });

        $penarikanHasil = $hasil['penarikan'];

        try {
            ActivityLogger::log('batalkan_penarikan', 'transaksi_penarikan', $penarikanHasil->id, [
                'nominal_diminta' => $penarikanHasil->nominal_diminta,
                'produk_id' => $penarikanHasil->produk_id,
                'status_sebelumnya' => $hasil['status_sebelumnya'],
                'saldo_dikembalikan' => $hasil['saldo_dikembalikan'],
                'alasan' => $penarikanHasil->alasan_batal,
            ]);

            $this->kirimNotifikasi($penarikanHasil);
        } catch (\Exception $e) {
            report($e);
        }

        return $penarikanHasil;
    }

    /**
     * Notifikasi in-app ke seluruh admin dan, bila pengambilan di rumah
     * kolektor, ke kolektor penanggung jawab nasabah.
     */
    private function kirimNotifikasi(TransaksiPenarikan $penarikan): void
    {
        $judul = 'Pengajuan Penarikan Dibatalkan';
        $pesan = 'Penarikan Rp '.number_format((float) $penarikan->nominal_diminta, 0, ',', '.')
            .' dibatalkan oleh nasabah.';

        foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
            ActivityLogger::notify((int) $adminId, $judul, $pesan);
        }

        if ($penarikan->lokasi_pengambilan !== 'rumah_kolektor') {
            return;
        }

        $kolektorId = KolektorNasabah::where('nasabah_id', $penarikan->nasabah_id)
            ->where('status', 'aktif')
            ->value('kolektor_id');

        if ($kolektorId !== null) {
            ActivityLogger::notify((int) $kolektorId, $judul, $pesan);
        }
    }
}
