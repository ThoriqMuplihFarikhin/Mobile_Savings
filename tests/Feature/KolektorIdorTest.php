<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

it('rejects kolektor input setoran for nasabah not assigned to them via IDOR', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor2->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('selectedNasabah', null)
        ->assertSet('nasabahId', '');
});

it('allows kolektor input setoran for their own assigned nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('nasabahId', (string) $nasabah->id)
        ->assertSet('selectedNasabah', fn ($val) => $val !== null);
});

it('rejects kolektor penarikan offline for nasabah not assigned to them via IDOR', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor2->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('selectedNasabah', null)
        ->assertSet('nasabahId', '');
});

it('allows kolektor penarikan offline for their own assigned nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('nasabahId', (string) $nasabah->id)
        ->assertSet('selectedNasabah', fn ($val) => $val !== null);
});
