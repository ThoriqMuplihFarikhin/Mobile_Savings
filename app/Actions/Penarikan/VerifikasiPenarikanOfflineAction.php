<?php

namespace App\Actions\Penarikan;

use App\Helpers\ActivityLogger;
use App\Models\KolektorNasabah;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class VerifikasiPenarikanOfflineAction
{
    private const MAX_PERCOBAAN = 3;

    private const DURASI_KUNCI_MENIT = 15;

    /**
     * Verifikasi bahwa nasabah benar-benar menerima uang penarikan offline,
     * dikonfirmasi langsung dengan PIN nasabah sendiri di hadapan kolektor.
     *
     * @throws \Exception
     */
    public function execute(TransaksiPenarikan $penarikan, string $pin, User $kolektor): TransaksiPenarikan
    {
        $penarikan = TransaksiPenarikan::where('id', $penarikan->id)
            ->lockForUpdate()
            ->first();

        if (! $penarikan) {
            throw new \Exception('Data penarikan tidak ditemukan.');
        }

        if ($penarikan->jalur_pengajuan !== 'offline') {
            throw new \Exception('Verifikasi langsung hanya berlaku untuk penarikan offline.');
        }

        if ($penarikan->status !== 'approved') {
            throw new \Exception('Penarikan ini belum disetujui admin atau sudah diproses.');
        }

        $isTanggungJawab = KolektorNasabah::where('kolektor_id', $kolektor->id)
            ->where('nasabah_id', $penarikan->nasabah_id)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            throw new \Exception('Nasabah ini bukan tanggung jawab Anda.');
        }

        if ($penarikan->sedangTerkunci()) {
            $sisaMenit = (int) now()->diffInMinutes($penarikan->terkunci_hingga);
            throw new \Exception("Verifikasi dikunci sementara karena PIN salah berulang kali. Coba lagi dalam {$sisaMenit} menit atau hubungi admin.");
        }

        $nasabah = $penarikan->nasabah;

        if (! $nasabah || ! Hash::check($pin, $nasabah->pin_hash)) {
            $percobaan = $penarikan->percobaan_verifikasi_gagal + 1;
            $update = ['percobaan_verifikasi_gagal' => $percobaan];

            if ($percobaan >= self::MAX_PERCOBAAN) {
                $update['terkunci_hingga'] = now()->addMinutes(self::DURASI_KUNCI_MENIT);
            }

            $penarikan->update($update);

            ActivityLogger::log('verifikasi_penarikan_gagal', 'transaksi_penarikan', $penarikan->id, [
                'nasabah_id' => $penarikan->nasabah_id,
                'kolektor_id' => $kolektor->id,
                'percobaan_ke' => $percobaan,
            ]);

            $sisa = self::MAX_PERCOBAAN - $percobaan;
            if ($sisa <= 0) {
                throw new \Exception('PIN salah. Verifikasi dikunci selama '.self::DURASI_KUNCI_MENIT.' menit karena melebihi batas percobaan.');
            }

            throw new \Exception("PIN salah. Sisa percobaan: {$sisa}.");
        }

        return DB::transaction(function () use ($penarikan, $kolektor) {
            $penarikan->update([
                'status' => 'selesai',
                'waktu_pencairan' => now(),
                'diverifikasi_oleh' => $kolektor->id,
                'metode_verifikasi' => 'pin_nasabah',
                'percobaan_verifikasi_gagal' => 0,
                'terkunci_hingga' => null,
            ]);

            ActivityLogger::log('verifikasi_penarikan_offline', 'transaksi_penarikan', $penarikan->id, [
                'nasabah_id' => $penarikan->nasabah_id,
                'kolektor_id' => $kolektor->id,
                'nominal' => $penarikan->nominal_diminta,
                'metode' => 'pin_nasabah',
            ]);

            ActivityLogger::notify(
                $penarikan->nasabah_id,
                'Penarikan Terverifikasi',
                'Penarikan Rp '.number_format($penarikan->nominal_diminta, 0, ',', '.').' telah diverifikasi dengan PIN Anda dan dinyatakan selesai.',
                'both'
            );

            return $penarikan;
        });
    }
}
