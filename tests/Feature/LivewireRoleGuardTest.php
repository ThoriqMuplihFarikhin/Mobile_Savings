<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Nasabah\Saldo;
use App\Models\User;
use Livewire\Livewire;
use Livewire\Mechanisms\PersistentMiddleware\PersistentMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

it('menolak kolektor memuat komponen admin', function () {
    $kolektor = User::factory()->kolektor()->create();

    Livewire::actingAs($kolektor)
        ->test(ApprovalPenarikan::class)
        ->assertForbidden();
});

it('menolak nasabah memuat komponen kolektor', function () {
    $nasabah = User::factory()->nasabah()->create();

    Livewire::actingAs($nasabah)
        ->test(InputSetoran::class)
        ->assertForbidden();
});

it('menolak admin memuat komponen nasabah', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Saldo::class)
        ->assertForbidden();
});

it('tetap mengizinkan admin memuat komponen admin', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(KelolaKolektor::class)
        ->assertSuccessful();
});

it('mendaftarkan active dan role sebagai persistent middleware livewire', function () {
    $middleware = app(PersistentMiddleware::class)->getPersistentMiddleware();

    expect($middleware)
        ->toContain(EnsureAccountIsActive::class)
        ->toContain(RoleMiddleware::class);
});
