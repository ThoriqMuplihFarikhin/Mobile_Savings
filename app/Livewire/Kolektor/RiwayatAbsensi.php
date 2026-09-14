<?php

namespace App\Livewire\Kolektor;

use App\Models\AbsensiKolektor;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.mobile')]
#[Title('Riwayat Absensi')]
class RiwayatAbsensi extends Component
{
    use WithPagination;

    public ?int $selectedAbsenId = null;

    public function selectAbsen(?int $id): void
    {
        $this->selectedAbsenId = $this->selectedAbsenId === $id ? null : $id;
    }

    public function render()
    {
        $riwayat = AbsensiKolektor::where('kolektor_id', auth()->id())
            ->latest('tanggal')
            ->latest('waktu_masuk')
            ->paginate(10);

        $selectedAbsen = $this->selectedAbsenId
            ? AbsensiKolektor::where('kolektor_id', auth()->id())->find($this->selectedAbsenId)
            : null;

        return view('livewire.kolektor.riwayat-absensi', compact('riwayat', 'selectedAbsen'));
    }
}
