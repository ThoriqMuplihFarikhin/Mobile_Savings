<?php

namespace App\Livewire\Concerns;

trait AuthorizesRole
{
    /**
     * Peran yang diizinkan memuat komponen ini.
     */
    abstract protected function requiredRole(): string;

    public function bootAuthorizesRole(): void
    {
        abort_unless(auth()->user()?->role === $this->requiredRole(), 403);
    }
}
