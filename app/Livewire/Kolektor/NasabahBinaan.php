<?php

namespace App\Livewire\Kolektor;

use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
class NasabahBinaan extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $nasabahIds = KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('status', 'aktif')
            ->pluck('nasabah_id');

        $query = NasabahProfil::whereIn('user_id', $nasabahIds)->with('user');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nama', 'like', "%{$this->search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('no_hp', 'like', "%{$this->search}%"));
            });
        }

        $nasabahList = $query->latest()->paginate(10);

        return view('livewire.kolektor.nasabah-binaan', compact('nasabahList'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
