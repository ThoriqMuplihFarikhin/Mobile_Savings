<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\AbsensiKolektor;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Monitoring Absensi')]
class MonitoringAbsensi extends Component
{
    use AuthorizesRole;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $tanggal;

    public ?int $selectedKolektorId = null;

    public function mount(): void
    {
        $this->tanggal = now()->toDateString();
    }

    public function selectKolektor(?int $id): void
    {
        $this->selectedKolektorId = $id;
    }

    public function render()
    {
        $tanggal = Carbon::parse($this->tanggal);
        $kolektors = User::where('role', 'kolektor')->get();
        $absensi = AbsensiKolektor::whereIn('kolektor_id', $kolektors->pluck('id'))
            ->where('tanggal', $this->tanggal)
            ->get()
            ->keyBy('kolektor_id');

        $selectedAbsen = $this->selectedKolektorId
            ? $absensi->get($this->selectedKolektorId)
            : null;

        return view('livewire.admin.monitoring-absensi', compact('kolektors', 'absensi', 'tanggal', 'selectedAbsen'));
    }
}
