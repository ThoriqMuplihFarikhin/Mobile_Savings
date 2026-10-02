<?php

namespace App\Actions\Penarikan;

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AjukanPenarikanAction
{
    public function execute(User $nasabah, ProdukTabungan $produk, float $nominal, string $jalur, string $lokasiPengambilan = 'kantor'): TransaksiPenarikan
    {
        return DB::transaction(function () use ($nasabah, $produk, $nominal, $jalur, $lokasiPengambilan) {
            $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->lockForUpdate()
                ->first();

            $totalPending = TransaksiPenarikan::where('nasabah_id', $nasabah->id)
                ->where('produk_id', $produk->id)
                ->where('status', 'pending')
                ->sum('nominal_diminta');

            $available = ($saldo->saldo ?? 0) - $totalPending;

            if ($available < $nominal) {
                throw new \Exception('Saldo tidak mencukupi! Sisa saldo tersedia: Rp '.number_format($available, 0, ',', '.'));
            }

            if ($produk->isPaket()) {
                if ($produk->tanggal_boleh_cair && now()->lt($produk->tanggal_boleh_cair)) {
                    throw new \Exception('Penarikan paket belum bisa dilakukan sebelum tanggal '.$produk->tanggal_boleh_cair->translatedFormat('d M Y'));
                }

                $statusTunggakan = (new HitungTunggakanAction)->execute($nasabah->id, $produk->id);

                if ($statusTunggakan && $statusTunggakan['tunggakan'] > 0) {
                    throw new \Exception('Penarikan paket belum bisa dilakukan karena masih ada tunggakan sebesar Rp '.number_format($statusTunggakan['tunggakan'], 0, ',', '.').'. Lunasi tunggakan terlebih dahulu atau hubungi admin.');
                }
            }

            $persenKomisi = $produk->persen_komisi ?? 0;
            $nominalKomisi = 0;
            $nominalDiterima = $nominal;

            if ($nominal > 0 && $persenKomisi > 0) {
                $nominalKomisi = ($nominal * $persenKomisi) / 100;
                $nominalDiterima = $nominal - $nominalKomisi;
            }

            return TransaksiPenarikan::create([
                'nasabah_id' => $nasabah->id,
                'produk_id' => $produk->id,
                'nominal_diminta' => $nominal,
                'persen_komisi_terpakai' => $persenKomisi,
                'nominal_komisi' => $nominalKomisi,
                'nominal_diterima' => $nominalDiterima,
                'jalur_pengajuan' => $jalur,
                'lokasi_pengambilan' => $lokasiPengambilan,
                'status' => 'pending',
            ]);
        });
    }
}
