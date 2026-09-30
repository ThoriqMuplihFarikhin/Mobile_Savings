<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class ProgresPaket extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function render()
    {
        $kepesertaan = KepesertaanPaket::with('produk')
            ->where('nasabah_id', Auth::id())
            ->latest('tanggal_mulai_ikut')
            ->get();

        foreach ($kepesertaan as $item) {
            $item->hitungUlangKepesertaan();
        }

        return view('livewire.nasabah.progres-paket', compact('kepesertaan'));
    }
}
