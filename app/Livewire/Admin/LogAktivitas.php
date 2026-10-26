<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\AuthorizesRole;
use App\Models\LogAktivitas as LogAktivitasModel;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LogAktivitas extends Component
{
    use AuthorizesRole;
    use WithPagination;

    protected function requiredRole(): string
    {
        return 'admin';
    }

    public string $search = '';

    public string $aksiFilter = '';

    public function render(): View
    {
        $query = LogAktivitasModel::with('user');

        if ($this->search) {
            $query->where('aksi', 'like', "%{$this->search}%")
                ->orWhere('entitas_terkait', 'like', "%{$this->search}%");
        }

        if ($this->aksiFilter) {
            $query->where('aksi', $this->aksiFilter);
        }

        $logs = $query->latest()->paginate(20);

        return view('livewire.admin.log-aktivitas', compact('logs'));
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedAksiFilter(): void
    {
        $this->resetPage();
    }
}
