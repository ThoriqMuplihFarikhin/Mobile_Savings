<?php

namespace App\Actions\Paket;

use App\Models\KepesertaanPaket;

/**
 * Alokasi total terkumpul ke harga tiap item isi paket secara berurutan
 * (D15). Item tanpa harga tidak dialokasi (status/persen null) - persen
 * keseluruhan hanya memakai item yang punya harga.
 */
class HitungProgresBarangAction
{
    /**
     * @return array{items: array<int, array{nama: mixed, jumlah: mixed, harga: float|null, dialokasikan: float, status: string|null, persen: float|null, sisa: float|null}>, persenKeseluruhan: float|null, totalHarga: float|null}
     */
    public function untukAdmin(KepesertaanPaket $kepesertaan): array
    {
        return $this->hitung($kepesertaan, true);
    }

    /**
     * Versi nasabah: tanpa harga/sisa/total, kecuali produk mengizinkan
     * `tampilkan_harga_ke_nasabah`.
     *
     * @return array{items: array<int, array{nama: mixed, jumlah: mixed, status: string|null, persen: float|null, harga?: float}>, persenKeseluruhan: float|null, totalHarga?: float}
     */
    public function untukNasabah(KepesertaanPaket $kepesertaan): array
    {
        $hasil = $this->hitung($kepesertaan, false);
        $tampilkanHarga = (bool) ($kepesertaan->produk?->tampilkan_harga_ke_nasabah);

        $items = array_map(function (array $item) use ($tampilkanHarga): array {
            $baris = [
                'nama' => $item['nama'],
                'jumlah' => $item['jumlah'],
                'status' => $item['status'],
                'persen' => $item['persen'],
            ];

            if ($tampilkanHarga && $item['harga'] !== null) {
                $baris['harga'] = $item['harga'];
            }

            return $baris;
        }, $hasil['items']);

        $hasil['items'] = $items;

        if ($tampilkanHarga) {
            $hasil['totalHarga'] = $hasil['totalHarga'];
        } else {
            unset($hasil['totalHarga']);
        }

        return $hasil;
    }

    /**
     * @return array{items: array<int, array{nama: mixed, jumlah: mixed, harga: float|null, dialokasikan: float, status: string|null, persen: float|null, sisa: float|null}>, persenKeseluruhan: float|null, totalHarga: float|null}
     */
    private function hitung(KepesertaanPaket $kepesertaan, bool $sertakanHarga): array
    {
        $items = $kepesertaan->produk->isi_paket ?? [];
        $totalHarga = 0.0;

        foreach ($items as $item) {
            $harga = $this->hargaItem($item);
            if ($harga !== null) {
                $totalHarga += $harga;
            }
        }

        $sisa = max(0.0, (float) $kepesertaan->total_aktual_terkumpul);
        $hasil = [];

        foreach ($items as $item) {
            $harga = $this->hargaItem($item);

            if ($harga === null) {
                $hasil[] = [
                    'nama' => $item['nama'] ?? '-',
                    'jumlah' => $item['jumlah'] ?? '-',
                    'harga' => null,
                    'dialokasikan' => 0.0,
                    'status' => null,
                    'persen' => null,
                    'sisa' => null,
                ];

                continue;
            }

            $dialokasikan = min($sisa, $harga);
            $sisa = max(0.0, $sisa - $dialokasikan);
            $status = $dialokasikan >= $harga
                ? 'tercapai'
                : ($dialokasikan > 0 ? 'berjalan' : 'belum');

            $hasil[] = [
                'nama' => $item['nama'] ?? '-',
                'jumlah' => $item['jumlah'] ?? '-',
                'harga' => $harga,
                'dialokasikan' => $dialokasikan,
                'status' => $status,
                'persen' => round($dialokasikan / $harga * 100, 2),
                'sisa' => round($harga - $dialokasikan, 2),
            ];
        }

        $persenKeseluruhan = $totalHarga > 0
            ? round(min(100.0, max(0.0, (float) $kepesertaan->total_aktual_terkumpul) / $totalHarga * 100), 2)
            : null;

        return [
            'items' => $hasil,
            'persenKeseluruhan' => $persenKeseluruhan,
            'totalHarga' => $totalHarga > 0 ? round($totalHarga, 2) : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function hargaItem(array $item): ?float
    {
        $harga = $item['harga'] ?? null;

        if (! is_numeric($harga) || (float) $harga <= 0) {
            return null;
        }

        return round((float) $harga, 2);
    }
}
