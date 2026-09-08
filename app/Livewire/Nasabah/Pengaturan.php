<?php

namespace App\Livewire\Nasabah;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportRedirects\Redirector;

#[Layout('layouts.mobile')]
class Pengaturan extends Component
{
    public $notifikasiWaAktif = true;

    public function mount(): void
    {
        $this->notifikasiWaAktif = Auth::user()->notifikasi_wa_aktif ?? true;
    }

    public function toggleNotifikasiWa(): void
    {
        $this->notifikasiWaAktif = ! $this->notifikasiWaAktif;
        Auth::user()->update(['notifikasi_wa_aktif' => $this->notifikasiWaAktif]);
    }

    public function logout(): Redirector|RedirectResponse
    {
        Auth::guard('web')->logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect('/');
    }

    public function render()
    {
        return view('livewire.nasabah.pengaturan');
    }
}
