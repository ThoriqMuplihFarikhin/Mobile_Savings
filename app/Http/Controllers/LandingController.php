<?php

namespace App\Http\Controllers;

use App\Models\ProdukTabungan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class LandingController extends Controller
{
    /**
     * Render halaman landing publik (tanpa metrik bisnis sesuai D10).
     * `isi_paket` lewat `isiPaketPublik()` agar harga item tidak bocor (D15).
     */
    public function __invoke(): View
    {
        $produkList = Cache::remember(
            'landing:produk:v2',
            now()->addMinutes(10),
            fn () => ProdukTabungan::where('status', 'aktif')
                ->get(['id', 'nama', 'tipe', 'harga_per_hari', 'minimal_setor', 'isi_paket'])
                ->each(function (ProdukTabungan $produk): void {
                    $produk->setAttribute('isi_paket', $produk->isiPaketPublik());
                })
        );

        return view('welcome', compact('produkList'));
    }
}
