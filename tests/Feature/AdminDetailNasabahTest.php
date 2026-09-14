<?php

use App\Livewire\Admin\DetailNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

it('allows admin to access detail nasabah via Livewire', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(DetailNasabah::class, ['user' => $nasabah])
        ->assertStatus(200)
        ->assertSet('user.id', $nasabah->id);
});

it('blocks kolektor from accessing detail nasabah via route', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($kolektor)
        ->get(route('admin.nasabah.detail', $nasabah->id))
        ->assertForbidden();
});

it('blocks nasabah from accessing detail nasabah via route', function () {
    $nasabah1 = User::factory()->nasabah()->create();
    $nasabah2 = User::factory()->nasabah()->create();

    $this->actingAs($nasabah1)
        ->get(route('admin.nasabah.detail', $nasabah2->id))
        ->assertForbidden();
});

it('returns 404 when accessing non-nasabah user via route', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($admin)
        ->get(route('admin.nasabah.detail', $kolektor->id))
        ->assertNotFound();
});
