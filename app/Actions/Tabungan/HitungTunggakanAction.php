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

        $kepesertaan = KepesertaanPaket::where('nasabah_id', $nasabahId)
            ->where('produk_id', $produkId)
            ->whereNull('keputusan_akhir')
            ->first();

        if (! $kepesertaan) {
            if (! $simpan) {
                return null;
            }

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

        $hasil = $kepesertaan->hitungUlangKepesertaan($simpan);

        $result = [
            'tunggakan' => $hasil['tunggakan_rupiah'],
            'tunggakan_hari' => $hasil['tunggakan_hari'],
            'hari' => $hasil['tunggakan_hari'],
            'seharusnya_hari_ini' => $kepesertaan->total_seharusnya_terkumpul,
        ];

        return $hasil['tunggakan_hari'] > 0 ? $result : null;
    }
}
