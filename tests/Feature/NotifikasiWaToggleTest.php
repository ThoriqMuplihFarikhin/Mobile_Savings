<?php

use App\Livewire\Kolektor\Pengaturan as KolektorPengaturan;
use App\Livewire\Nasabah\Pengaturan as NasabahPengaturan;
use App\Models\User;
use Livewire\Livewire;

it('lets kolektor toggle notifikasi wa via shared trait', function () {
    $kolektor = User::factory()->kolektor()->create(['notifikasi_wa_aktif' => true]);

    $this->actingAs($kolektor);

    Livewire::test(KolektorPengaturan::class)
        ->assertSet('notifikasiWaAktif', true)
        ->call('toggleNotifikasiWa')
        ->assertSet('notifikasiWaAktif', false);

    expect($kolektor->refresh()->notifikasi_wa_aktif)->toBeFalse();
});

it('lets nasabah toggle notifikasi wa via shared trait', function () {
    $nasabah = User::factory()->nasabah()->create(['notifikasi_wa_aktif' => false]);

    $this->actingAs($nasabah);

    Livewire::test(NasabahPengaturan::class)
        ->assertSet('notifikasiWaAktif', false)
        ->call('toggleNotifikasiWa')
        ->assertSet('notifikasiWaAktif', true);

    expect($nasabah->refresh()->notifikasi_wa_aktif)->toBeTrue();
});
