<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\HasNotifikasiWaToggle;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.mobile')]
class Pengaturan extends Component
{
    use HasNotifikasiWaToggle;

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
        return view('livewire.kolektor.pengaturan');
    }
}
