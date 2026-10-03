<?php

namespace App\Actions\Tabungan;

use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;

class HitungTunggakanAction
{
    public function execute(int $nasabahId, int $produkId, bool $simpan = false): ?array
    {
        $produk = ProdukTabungan::find($produkId);
        if (! $produk || $produk->tipe !== 'paket' || ! $produk->harga_per_hari) {
            return null;
        }

        $kepesertaan = $this->kepesertaanAktif($nasabahId, $produkId, $simpan);
        if (! $kepesertaan) {
            return null;
        }

        $hasil = $kepesertaan->hitungUlangKepesertaan($simpan);

        $result = [
            'kepesertaan_id' => $kepesertaan->id,
            'tunggakan' => $hasil['tunggakan_rupiah'],
            'tunggakan_hari' => $hasil['tunggakan_hari'],
            'hari' => $hasil['tunggakan_hari'],
            'seharusnya_hari_ini' => $kepesertaan->total_seharusnya_terkumpul,
        ];

        return $hasil['tunggakan_hari'] > 0 ? $result : null;
    }

    /**
     * Kepesertaan yang sedang berjalan (belum ada keputusan akhir) untuk
     * pasangan nasabah+produk; dibuat bila diminta dan belum ada.
     */
    public function kepesertaanAktif(int $nasabahId, int $produkId, bool $buatJikaBelumAda = false): ?KepesertaanPaket
    {
        $kepesertaan = KepesertaanPaket::where('nasabah_id', $nasabahId)
            ->where('produk_id', $produkId)
            ->whereNull('keputusan_akhir')
            ->first();

        if (! $kepesertaan && $buatJikaBelumAda) {
            $kepesertaan = KepesertaanPaket::create([
                'nasabah_id' => $nasabahId,
                'produk_id' => $produkId,
                'tanggal_mulai_ikut' => now()->toDateString(),
                'total_seharusnya_terkumpul' => 0,
                'total_aktual_terkumpul' => 0,
                'tunggakan' => 0,
                'status_alert' => 'normal',
            ]);
        }

        return $kepesertaan;
    }
}
