<?php

namespace App\Livewire\Concerns;

trait AuthorizesRoles
{
    /**
     * Daftar peran yang diizinkan memuat komponen ini.
     *
     * @return array<int, string>
     */
    abstract protected function requiredRoles(): array;

    public function bootAuthorizesRoles(): void
    {
        abort_unless(in_array(auth()->user()?->role, $this->requiredRoles(), true), 403);
    }
}
