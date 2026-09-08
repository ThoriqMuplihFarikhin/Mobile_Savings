<?php

namespace App\Livewire\Nasabah;

use App\Models\SaldoProduk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class Saldo extends Component
{
    public function render()
    {
        $saldo = SaldoProduk::where('nasabah_id', Auth::id())
            ->with('produk')
            ->get();

        $totalSaldo = $saldo->sum('saldo');

        return view('livewire.nasabah.saldo', compact('saldo', 'totalSaldo'));
    }
}
