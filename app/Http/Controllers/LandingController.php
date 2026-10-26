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
     * Nilai cache berupa **array murni** - bukan objek Eloquent - supaya aman
     * di-store apa pun tanpa allowlist `serializable_classes` (F4/D29).
     */
    public function __invoke(): View
    {
        $produkList = Cache::remember(
            'landing:produk:v3',
            now()->addMinutes(10),
            fn (): array => ProdukTabungan::where('status', 'aktif')
                ->get(['id', 'nama', 'tipe', 'harga_per_hari', 'minimal_setor', 'isi_paket'])
                ->map(fn (ProdukTabungan $produk): array => [
                    'id' => $produk->id,
                    'nama' => $produk->nama,
                    'tipe' => $produk->tipe,
                    'harga_per_hari' => $produk->harga_per_hari,
                    'minimal_setor' => $produk->minimal_setor,
                    'isi_paket' => $produk->isiPaketPublik(),
                ])
                ->all()
        );

        return view('welcome', compact('produkList'));
    }
}
