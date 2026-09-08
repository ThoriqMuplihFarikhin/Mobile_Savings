<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait HasNotifikasiWaToggle
{
    public bool $notifikasiWaAktif = true;

    public function mountNotifikasiWaToggle(): void
    {
        $this->notifikasiWaAktif = Auth::user()->notifikasi_wa_aktif ?? true;
    }

    public function toggleNotifikasiWa(): void
    {
        $this->notifikasiWaAktif = ! $this->notifikasiWaAktif;
        Auth::user()->update(['notifikasi_wa_aktif' => $this->notifikasiWaAktif]);
    }
}
