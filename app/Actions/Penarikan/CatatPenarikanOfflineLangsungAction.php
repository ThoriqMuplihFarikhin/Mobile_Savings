<?php

namespace App\Actions\Penarikan;

use App\Helpers\ActivityLogger;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Illuminate\Support\Facades\DB;

/**
 * Catat penarikan tunai kolektor untuk nasabah mode offline (D14, §5.3.5).
 *
 * Validasi saldo (lockForUpdate), nominal minimal, gerbang paket, dan
 * tunggakan dipinjam dari AjukanPenarikanAction. Bila pengaturan
 * `offline_penarikan_langsung_selesai` aktif dan nominal tidak melewati
 * ambang persetujuan ganda, penarikan langsung diselesaikan tanpa PIN
 * atau verifikasi; selain itu masuk antrian approval admin seperti biasa.
 * Kas kolektor (D13) hanya berkurang untuk pengambilan di rumah kolektor.
 */
class CatatPenarikanOfflineLangsungAction
{
    /**
     * @param  string  $catatan  Catatan wajib penarikan offline (tersimpan di log aktivitas).
     * @return array{penarikan: TransaksiPenarikan, langsung: bool, alasan: string}
     *
     * @throws \Exception bila validasi ditolak
     */
    public function execute(
        User $kolektor,
        User $nasabah,
        ProdukTabungan $produk,
        float|string $nominal,
        string $lokasiPengambilan = 'kantor',
        string $catatan = '',
    ): array {
        $isTanggungJawab = KolektorNasabah::where('kolektor_id', $kolektor->id)
            ->where('nasabah_id', $nasabah->id)
            ->where('status', 'aktif')
            ->exists();

        if (! $isTanggungJawab) {
            throw new \Exception('Nasabah ini bukan tanggung jawab Anda.');
        }

        $penarikan = (new AjukanPenarikanAction)->execute(
            $nasabah,
            $produk,
            $nominal,
            'offline',
            $lokasiPengambilan,
        );

        $alasan = $this->alasanTidakLangsung($nasabah, $penarikan);

        if ($alasan !== null) {
            return ['penarikan' => $penarikan, 'langsung' => false, 'alasan' => $alasan];
        }

        $hasil = DB::transaction(function () use ($penarikan, $kolektor, $lokasiPengambilan, $catatan) {
            $p = TransaksiPenarikan::whereKey($penarikan->id)->lockForUpdate()->first();

            if (! $p || $p->status !== 'pending') {
                return ['penarikan' => $penarikan, 'langsung' => false, 'alasan' => 'tidak_pending'];
            }

            $saldo = SaldoProduk::where('nasabah_id', $p->nasabah_id)
                ->where('produk_id', $p->produk_id)
                ->lockForUpdate()
                ->first();

            if (! $saldo || bccomp((string) $saldo->saldo, (string) $p->nominal_diminta, 2) < 0) {
                throw new \Exception('Saldo nasabah tidak mencukupi!');
            }

            $diRumah = $lokasiPengambilan === 'rumah_kolektor';
            $kasSebelum = null;

            if ($diRumah) {
                // D13: kas kolektor harus cukup membayar penarikan tunai ini.
                KasKolektorHitung::kunciBarisKas($kolektor->id);
                $kasSebelum = KasKolektorHitung::kasDiTangan($kolektor->id);

                if (! KasKolektorHitung::bolehKasMinus()
                    && bccomp($kasSebelum, (string) $p->nominal_diterima, 2) < 0) {
                    // Guard gagal → penarikan tetap di antrian approval admin.
                    return ['penarikan' => $penarikan, 'langsung' => false, 'alasan' => 'kas_kurang'];
                }
            }

            $saldo->decrement('saldo', $p->nominal_diminta);

            $p->update([
                'status' => 'selesai',
                'jalur_pengajuan' => 'offline_kolektor',
                'metode_verifikasi' => 'tanpa_verifikasi_offline',
                'diverifikasi_oleh' => $kolektor->id,
                'dibayar_oleh' => $kolektor->id,
                'mempengaruhi_kas' => $diRumah,
                'waktu_approval' => now(),
                'waktu_pencairan' => now(),
            ]);

            if ($diRumah) {
                // D13: tautkan ke pengajuan setor pending dan perbarui total net.
                $pengajuanPending = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)
                    ->where('status', 'pending')
                    ->lockForUpdate()
                    ->first();

                if ($pengajuanPending) {
                    $p->update(['setoran_kolektor_id' => $pengajuanPending->id]);
                    $pengajuanPending->update([
                        'total_seharusnya' => KasKolektorHitung::totalSeharusnyaPengajuan($pengajuanPending->id),
                    ]);
                }

                ActivityLogger::log('kas_berkurang_penarikan_tunai', 'transaksi_penarikan', $p->id, [
                    'kolektor_id' => $kolektor->id,
                    'nominal_diterima' => $p->nominal_diterima,
                    'kas_sebelum' => $kasSebelum,
                    'kas_sesudah' => bcsub($kasSebelum, (string) $p->nominal_diterima, 2),
                    'jalur' => 'offline_kolektor',
                ]);
            }

            ActivityLogger::log('selesai_penarikan_offline', 'transaksi_penarikan', $p->id, [
                'nasabah_id' => $p->nasabah_id,
                'kolektor_id' => $kolektor->id,
                'nominal' => $p->nominal_diminta,
                'metode' => 'tanpa_verifikasi_offline',
                'catatan' => $catatan,
            ]);

            return ['penarikan' => $p, 'langsung' => true, 'alasan' => 'langsung'];
        });

        if ($hasil['langsung']) {
            $this->notifikasiAdmin($hasil['penarikan']);
        }

        return $hasil;
    }

    /**
     * Alasan penarikan tidak boleh langsung selesai, atau null bila layak.
     */
    private function alasanTidakLangsung(User $nasabah, TransaksiPenarikan $penarikan): ?string
    {
        if (! $nasabah->isOffline()) {
            return 'digital';
        }

        if (AdminSetting::get('offline_penarikan_langsung_selesai', 'true') !== 'true') {
            return 'setting_nonaktif';
        }

        if (ApprovalPenarikan::melewatiAmbangDuaApprover((float) $penarikan->nominal_diminta)) {
            return 'melewati_ambang';
        }

        return null;
    }

    /**
     * Notifikasi in-app ke seluruh admin pasca-commit (bukan ke nasabah offline).
     */
    private function notifikasiAdmin(TransaksiPenarikan $penarikan): void
    {
        try {
            foreach (User::where('role', 'admin')->pluck('id') as $adminId) {
                ActivityLogger::notify(
                    (int) $adminId,
                    'Penarikan Offline Selesai',
                    'Penarikan Rp '.number_format((float) $penarikan->nominal_diminta, 0, ',', '.')
                        .' tanpa verifikasi PIN telah langsung diselesaikan kolektor.',
                );
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
