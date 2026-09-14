<?php

namespace App\Livewire\Kolektor;

use App\Livewire\Actions\Logout;
use App\Livewire\Concerns\HasNotifikasiWaToggle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Layout('layouts.mobile')]
class Pengaturan extends Component
{
    use HasNotifikasiWaToggle;

    /**
     * Info khusus kolektor: apakah masih ada setoran yang belum
     * disetor ke kantor (agar diingatkan lewat halaman pengaturan).
     */
    public bool $adaKasBelumDisetor = false;

    public function mount(): void
    {
        $this->mountNotifikasiWaToggle();
        $this->adaKasBelumDisetor = Auth::user()->hasUnsettledCash();
    }

    public function logout(): Redirector|RedirectResponse
    {
        return app(Logout::class)();
    }

    public function render()
    {
        return view('livewire.kolektor.pengaturan');
    }
}
