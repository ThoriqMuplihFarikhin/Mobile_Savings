<?php

namespace App\Support;

use App\Models\AdminSetting;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use Illuminate\Database\Eloquent\Builder;

/**
 * Sumber tunggal perhitungan kas kolektor (keputusan D13):
 *
 * kas_di_tangan(k) = Σ setoran belum diterima kantor (k)
 *                    − Σ nominal_diterima penarikan tunai yang dibayarkan
 *                      kolektor k dan belum direkonsiliasi
 *
 * "Belum direkonsiliasi" mencakup penarikan yang belum tertaut maupun tertaut
 * ke pengajuan setor yang masih pending — uang fisik belum berpindah ke kantor,
 * jadi tetap mengurangi kas di tangan dan sejalan dengan total_seharusnya
 * pengajuan (ASUMSI D13a, lihat docs/plan-log.md).
 *
 * Seluruh angka kas di aplikasi (dashboard, handover, rekonsiliasi, laporan)
 * wajib melewati kelas ini agar konsisten satu sama lain.
 */
class KasKolektorHitung
{
    /**
     * Query penarikan tunai belum direkonsiliasi; null = seluruh kolektor
     * (dipakai agregat tabel/kartu admin).
     *
     * @return Builder<TransaksiPenarikan>
     */
    public static function queryTunaiKeluar(?int $kolektorId): Builder
    {
        $query = TransaksiPenarikan::query()
            ->where('status', 'selesai')
            ->where('lokasi_pengambilan', 'rumah_kolektor')
            ->where('mempengaruhi_kas', true)
            ->where(function (Builder $sub) {
                $sub->whereNull('setoran_kolektor_id')
                    ->orWhereHas('setoranKolektor', fn (Builder $pengajuan) => $pengajuan->where('status', 'pending'));
            });

        if ($kolektorId !== null) {
            $query->where('dibayar_oleh', $kolektorId);
        }

        return $query;
    }

    /**
     * Total penarikan tunai kolektor yang belum direkonsiliasi (skala 2).
     *
     * @return numeric-string
     */
    public static function tunaiKeluarBelumDirekonsiliasi(int $kolektorId): string
    {
        return self::bc(self::queryTunaiKeluar($kolektorId)->sum('nominal_diterima'));
    }

    /**
     * Kas fisik yang dipegang kolektor sesuai rumus D13 (skala 2).
     *
     * @return numeric-string
     */
    public static function kasDiTangan(int $kolektorId): string
    {
        $setoran = TransaksiSetoran::belumDisetor()
            ->where('input_by', $kolektorId)
            ->sum('nominal');

        return bcsub(self::bc($setoran), self::tunaiKeluarBelumDirekonsiliasi($kolektorId), 2);
    }

    /**
     * Penarikan tunai per kolektor dalam satu query GROUP BY (tabel/kartu admin),
     * berasal dari scope yang sama dengan kasDiTangan.
     *
     * @return array<int, float>
     */
    public static function tunaiKeluarPerKolektor(): array
    {
        $rows = self::queryTunaiKeluar(null)
            ->groupBy('dibayar_oleh')
            ->selectRaw('dibayar_oleh, COALESCE(SUM(nominal_diterima), 0) as total_tunai')
            ->get();

        $hasil = [];
        foreach ($rows as $row) {
            $hasil[(int) $row->getAttribute('dibayar_oleh')] = (float) $row->getAttribute('total_tunai');
        }

        return $hasil;
    }

    /**
     * Kas di tangan per kolektor (D13) dalam dua query agregat — dipakai
     * laporan per kolektor agar tidak N+1 per baris.
     *
     * @return array<int, float>
     */
    public static function kasDiTanganPerKolektor(): array
    {
        $setoranPerId = [];
        foreach (TransaksiSetoran::belumDisetor()
            ->whereNotNull('input_by')
            ->groupBy('input_by')
            ->selectRaw('input_by, COALESCE(SUM(nominal), 0) as total_setoran')
            ->get() as $baris) {
            $setoranPerId[(int) $baris->getAttribute('input_by')] = self::bc($baris->getAttribute('total_setoran'));
        }

        $tunaiPerId = self::tunaiKeluarPerKolektor();
        $hasil = [];

        foreach ($setoranPerId as $id => $setoran) {
            $hasil[$id] = (float) bcsub($setoran, self::bc($tunaiPerId[$id] ?? 0.0), 2);
        }

        foreach ($tunaiPerId as $id => $tunai) {
            $hasil[$id] ??= (float) bcsub('0.00', self::bc($tunai), 2);
        }

        return $hasil;
    }

    /**
     * Kunci baris setoran + penarikan milik kolektor di dalam transaksi
     * sebelum menghitung kas, mencegah pembayaran ganda yang berjalan paralel.
     * Wajib dipanggil dari dalam DB::transaction.
     */
    public static function kunciBarisKas(int $kolektorId): void
    {
        TransaksiSetoran::belumDisetor()
            ->where('input_by', $kolektorId)
            ->lockForUpdate()
            ->get(['id']);

        self::queryTunaiKeluar($kolektorId)->lockForUpdate()->get(['id']);
    }

    /**
     * Total seharusnya sebuah pengajuan setor: setoran tertaut (non-dibatalkan)
     * dikurangi penarikan tunai tertaut (skala 2). Pemanggil wajib di dalam
     * transaksi berlock.
     *
     * @return numeric-string
     */
    public static function totalSeharusnyaPengajuan(int $pengajuanId): string
    {
        $setoran = TransaksiSetoran::where('setoran_kolektor_id', $pengajuanId)
            ->where('status', '!=', 'dibatalkan')
            ->lockForUpdate()
            ->sum('nominal');

        $tunai = TransaksiPenarikan::where('setoran_kolektor_id', $pengajuanId)
            ->where('mempengaruhi_kas', true)
            ->lockForUpdate()
            ->sum('nominal_diterima');

        return bcsub(self::bc($setoran), self::bc($tunai), 2);
    }

    /**
     * Apakah admin mengizinkan kas kolektor minus (AdminSetting D13, default false).
     */
    public static function bolehKasMinus(): bool
    {
        return AdminSetting::get('izinkan_kas_minus', 'false') === 'true';
    }

    /**
     * Normalisasi nilai SQL/bcmath ke string skala 2.
     *
     * @return numeric-string
     */
    private static function bc(mixed $nilai): string
    {
        if ($nilai === null || $nilai === '') {
            return '0.00';
        }

        $teks = (string) $nilai;

        if (! is_numeric($teks)) {
            return '0.00';
        }

        return bcadd($teks, '0', 2);
    }
}
