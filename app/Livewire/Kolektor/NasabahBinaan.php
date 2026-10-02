<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\SaldoProduk;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class NasabahBinaan extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'kolektor';
    }

    public $search = '';

    public $filterTunggakan = 'semua';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterTunggakan()
    {
        $this->resetPage();
    }

    public function render()
    {
        $kolektorId = Auth::id();

        $nasabahIds = KolektorNasabah::where('kolektor_id', $kolektorId)
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $query = NasabahProfil::whereIn('user_id', $nasabahIds)
            ->with(['user', 'user.saldoProduks.produk', 'user.kepesertaanPakets']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhere('alamat', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('no_hp', 'like', "%{$this->search}%"));
            });
        }

        if ($this->filterTunggakan === 'tunggakan') {
            $query->whereHas('user.kepesertaanPakets', function ($q) {
                $q->whereNull('keputusan_akhir')->where('tunggakan', '>', 0);
            });
        }

        $nasabahList = $query->latest()->paginate(10);

        $totalNasabah = $nasabahIds->count();

        $totalSaldoDikelola = SaldoProduk::whereIn('nasabah_id', $nasabahIds)->sum('saldo');

        $totalNasabahTunggakan = KepesertaanPaket::whereIn('nasabah_id', $nasabahIds)
            ->whereNull('keputusan_akhir')
            ->where('tunggakan', '>', 0)
            ->distinct('nasabah_id')
            ->count('nasabah_id');

        return view('livewire.kolektor.nasabah-binaan', compact(
            'nasabahList',
            'totalNasabah',
            'totalSaldoDikelola',
            'totalNasabahTunggakan'
        ));
    }
}
