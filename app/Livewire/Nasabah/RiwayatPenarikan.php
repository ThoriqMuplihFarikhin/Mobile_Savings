<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\TransaksiPenarikan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class RiwayatPenarikan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function render()
    {
        $penarikan = TransaksiPenarikan::with('produk')
            ->where('nasabah_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('livewire.nasabah.riwayat-penarikan', compact('penarikan'));
    }
}
