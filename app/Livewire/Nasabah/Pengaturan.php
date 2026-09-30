<?php

namespace App\Livewire\Nasabah;

use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\AuthorizesRole;
use App\Livewire\Concerns\HasNotifikasiWaToggle;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class Pengaturan extends Component
{
    use AuthorizesRole;
    use HasNotifikasiWaToggle;

    protected function requiredRole(): string
    {
        return 'nasabah';
    }

    public function mount(): void
    {
        $this->mountNotifikasiWaToggle();
    }

    public function logout(Logout $logout)
    {
        return $logout();
    }

    public function render()
    {
        return view('livewire.nasabah.pengaturan');
    }
}
