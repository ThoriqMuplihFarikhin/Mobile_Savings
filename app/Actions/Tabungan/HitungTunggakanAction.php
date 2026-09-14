<?php

namespace App\Actions\Tabungan;

use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use Carbon\Carbon;

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

        $hariBerjalan = Carbon::parse($kepesertaan->tanggal_mulai_ikut)->diffInDays(now());
        $seharusnya = $hariBerjalan * $produk->harga_per_hari;

        $aktual = TransaksiSetoran::where('nasabah_id', $nasabahId)
            ->where('produk_id', $produkId)
            ->where('status', '!=', 'dibatalkan')
            ->sum('nominal');

        $tunggakan = max(0, $seharusnya - $aktual);

        $statusAlert = 'normal';
        if ($tunggakan > 0) {
            $statusAlert = 'peringatan';
            if ($produk->batas_toleransi_tunggakan_hari && $hariBerjalan >= $produk->batas_toleransi_tunggakan_hari) {
                $statusAlert = 'perlu_review';
            }
        }

        $result = [
            'tunggakan' => $tunggakan,
            'hari' => $hariBerjalan,
            'seharusnya_hari_ini' => $seharusnya,
        ];

        if ($simpan) {
            $kepesertaan->update([
                'total_seharusnya_terkumpul' => $seharusnya,
                'total_aktual_terkumpul' => $aktual,
                'tunggakan' => $tunggakan,
                'status_alert' => $statusAlert,
            ]);
        }

        return $tunggakan > 0 ? $result : null;
    }
}
