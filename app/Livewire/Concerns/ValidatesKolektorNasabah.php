<?php

namespace App\Livewire\Concerns;

use App\Models\KolektorNasabah;
use Illuminate\Support\Facades\Auth;

trait ValidatesKolektorNasabah
{
    protected function isNasabahBinaan(int $nasabahId): bool
    {
        return KolektorNasabah::where('kolektor_id', Auth::id())
            ->where('nasabah_id', $nasabahId)
            ->where('status', 'aktif')
            ->exists();
    }
}
