<?php

namespace App\Actions\Penarikan;

use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;

class AjukanPenarikanAction
{
    public function execute(User $nasabah, ProdukTabungan $produk, float $nominal, string $jalur, string $lokasiPengambilan = 'kantor'): TransaksiPenarikan
    {
        $saldo = $produk->saldoProduks()
            ->where('nasabah_id', $nasabah->id)
            ->first();

        $totalPending = TransaksiPenarikan::where('nasabah_id', $nasabah->id)
            ->where('produk_id', $produk->id)
            ->where('status', 'pending')
            ->sum('nominal_diminta');

        $available = ($saldo->saldo ?? 0) - $totalPending;

        if ($available < $nominal) {
            throw new \Exception('Saldo tidak mencukupi! Sisa saldo tersedia: Rp '.number_format($available, 0, ',', '.'));
        }

        if ($produk->isPaket() && $produk->tanggal_boleh_cair && now()->lt($produk->tanggal_boleh_cair)) {
            throw new \Exception('Penarikan paket belum bisa dilakukan sebelum tanggal '.$produk->tanggal_boleh_cair->translatedFormat('d M Y'));
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
    }
}
