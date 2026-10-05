<?php

namespace App\Http\Controllers;

use App\Models\ProdukTabungan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;

class LandingController extends Controller
{
    /**
     * Render halaman landing publik (tanpa metrik bisnis sesuai D10).
     */
    public function __invoke(): View
    {
        $produkList = Cache::remember(
            'landing:produk',
            now()->addMinutes(10),
            fn () => ProdukTabungan::where('status', 'aktif')
                ->get(['id', 'nama', 'tipe', 'harga_per_hari', 'minimal_setor', 'isi_paket'])
        );

        return view('welcome', compact('produkList'));
    }
}
