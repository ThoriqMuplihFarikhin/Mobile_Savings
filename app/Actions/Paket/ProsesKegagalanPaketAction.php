<?php

namespace App\Actions\Paket;

use App\Helpers\ActivityLogger;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\DB;

class ProsesKegagalanPaketAction
{
    /**
     * Gagal dikembalikan: buat penarikan offline pending senilai seluruh saldo
     * produk lalu tandai keputusan; penyelesaiannya lewat approval biasa.
     *
     * @throws \DomainException bila kepesertaan sudah diputuskan atau tidak ada
     */
    public function dikembalikan(int $kepesertaanId, string $metodePengambilan, string $catatan = ''): TransaksiPenarikan
    {
        $hasil = DB::transaction(function () use ($kepesertaanId, $metodePengambilan, $catatan) {
            $kepesertaan = KepesertaanPaket::whereKey($kepesertaanId)->lockForUpdate()->first();

            if (! $kepesertaan) {
                throw new \DomainException('Kepesertaan tidak ditemukan.');
            }

            if ($kepesertaan->keputusan_akhir !== null) {
                throw new \DomainException('Kepesertaan sudah memiliki keputusan akhir.');
            }

            $saldo = SaldoProduk::where('nasabah_id', $kepesertaan->nasabah_id)
                ->where('produk_id', $kepesertaan->produk_id)
                ->lockForUpdate()
                ->first();

            $nominal = (float) ($saldo->saldo ?? 0);
            $lokasi = $metodePengambilan === 'diantar_kolektor' ? 'rumah_kolektor' : 'kantor';

            $penarikan = TransaksiPenarikan::create([
                'nasabah_id' => $kepesertaan->nasabah_id,
                'produk_id' => $kepesertaan->produk_id,
                'nominal_diminta' => $nominal,
                'persen_komisi_terpakai' => 0,
                'nominal_komisi' => 0,
                'nominal_diterima' => $nominal,
                'jalur_pengajuan' => 'offline',
                'lokasi_pengambilan' => $lokasi,
                'status' => 'pending',
            ]);

            $kepesertaan->update([
                'keputusan_akhir' => 'gagal_dikembalikan',
                'catatan_admin' => $catatan !== '' ? $catatan : $kepesertaan->catatan_admin,
                'metode_pengambilan' => $metodePengambilan,
            ]);

            return ['kepesertaan' => $kepesertaan, 'penarikan' => $penarikan, 'nominal' => $nominal];
        });

        try {
            ActivityLogger::log('keputusan_paket', 'kepesertaan_paket', $hasil['kepesertaan']->id, [
                'keputusan' => 'gagal_dikembalikan',
                'nasabah_id' => $hasil['kepesertaan']->nasabah_id,
                'produk_id' => $hasil['kepesertaan']->produk_id,
                'penarikan_id' => $hasil['penarikan']->id,
                'nominal' => $hasil['nominal'],
                'metode_pengambilan' => $metodePengambilan,
                'catatan' => $catatan,
            ]);

            ActivityLogger::notify(
                $hasil['kepesertaan']->nasabah_id,
                'Keputusan Kepesertaan Paket',
                'Kepesertaan paket Anda dinyatakan gagal dan akan dikembalikan Rp '.number_format($hasil['nominal'], 0, ',', '.').' melalui pencairan offline.',
                'both',
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $hasil['penarikan'];
    }

    /**
     * Gagal dialihkan: transfer seluruh saldo produk paket ke produk tujuan
     * secara atomik (lock dua baris berurutan id) lalu tandai keputusan.
     *
     * @throws \DomainException bila kepesertaan sudah diputuskan atau produk tujuan tidak valid
     */
    public function dialihkan(int $kepesertaanId, int $produkTujuanId, string $catatan = ''): float
    {
        $hasil = DB::transaction(function () use ($kepesertaanId, $produkTujuanId, $catatan) {
            $kepesertaan = KepesertaanPaket::whereKey($kepesertaanId)->lockForUpdate()->first();

            if (! $kepesertaan) {
                throw new \DomainException('Kepesertaan tidak ditemukan.');
            }

            if ($kepesertaan->keputusan_akhir !== null) {
                throw new \DomainException('Kepesertaan sudah memiliki keputusan akhir.');
            }

            $produkTujuan = ProdukTabungan::find($produkTujuanId);

            if (! $produkTujuan || $produkTujuan->status !== 'aktif' || $produkTujuan->id === $kepesertaan->produk_id) {
                throw new \DomainException('Produk tujuan tidak valid. Pilih produk aktif selain paket ini.');
            }

            $baris = SaldoProduk::where('nasabah_id', $kepesertaan->nasabah_id)
                ->whereIn('produk_id', [$kepesertaan->produk_id, $produkTujuan->id])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $saldoSumber = $baris->firstWhere('produk_id', $kepesertaan->produk_id);
            $saldoTujuan = $baris->firstWhere('produk_id', $produkTujuan->id);

            if (! $saldoTujuan) {
                $saldoTujuan = SaldoProduk::create([
                    'nasabah_id' => $kepesertaan->nasabah_id,
                    'produk_id' => $produkTujuan->id,
                    'saldo' => 0,
                ]);
            }

            $nominal = (float) ($saldoSumber->saldo ?? 0);

            if ($saldoSumber && $nominal > 0) {
                $saldoSumber->decrement('saldo', $nominal);
                $saldoTujuan->increment('saldo', $nominal);
            }

            $kepesertaan->update([
                'keputusan_akhir' => 'gagal_dialihkan',
                'catatan_admin' => $catatan !== '' ? $catatan : $kepesertaan->catatan_admin,
            ]);

            return [
                'kepesertaan' => $kepesertaan,
                'produkTujuan' => $produkTujuan,
                'nominal' => $nominal,
            ];
        });

        try {
            ActivityLogger::log('keputusan_paket', 'kepesertaan_paket', $hasil['kepesertaan']->id, [
                'keputusan' => 'gagal_dialihkan',
                'nasabah_id' => $hasil['kepesertaan']->nasabah_id,
                'produk_asal_id' => $hasil['kepesertaan']->produk_id,
                'produk_tujuan_id' => $hasil['produkTujuan']->id,
                'nominal' => $hasil['nominal'],
                'catatan' => $catatan,
            ]);

            ActivityLogger::log('alih_saldo_paket', 'kepesertaan_paket', $hasil['kepesertaan']->id, [
                'nasabah_id' => $hasil['kepesertaan']->nasabah_id,
                'dari_produk_id' => $hasil['kepesertaan']->produk_id,
                'ke_produk_id' => $hasil['produkTujuan']->id,
                'nominal' => $hasil['nominal'],
            ]);

            ActivityLogger::notify(
                $hasil['kepesertaan']->nasabah_id,
                'Keputusan Kepesertaan Paket',
                'Sisa saldo paket Anda Rp '.number_format($hasil['nominal'], 0, ',', '.').' telah dialihkan ke produk '.$hasil['produkTujuan']->nama.'.',
                'both',
            );
        } catch (\Throwable $e) {
            report($e);
        }

        return $hasil['nominal'];
    }
}
