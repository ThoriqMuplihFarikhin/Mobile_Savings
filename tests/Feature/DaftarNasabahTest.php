<?php

use App\Livewire\Admin\VerifikasiNasabah;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

it('stores profile fields when kolektor registers a nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(DaftarNasabah::class)
        ->call('toggleForm')
        ->set('nama', 'Budi Santoso')
        ->set('noHp', '081234567890')
        ->set('alamat', 'Jl. Merdeka No. 1')
        ->set('tanggalLahir', '1990-05-15')
        ->set('jenisKelamin', 'perempuan')
        ->set('pekerjaan', 'Pedagang')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('nasabah_profil', [
        'nama' => 'Budi Santoso',
        'tanggal_lahir' => '1990-05-15',
        'jenis_kelamin' => 'perempuan',
        'pekerjaan' => 'Pedagang',
    ]);
});

it('shows profile fields on admin verification page', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Budi Santoso',
        'alamat' => 'Jl. Merdeka No. 1',
        'tanggal_lahir' => '1990-05-15',
        'jenis_kelamin' => 'perempuan',
        'pekerjaan' => 'Pedagang',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'pending_verifikasi',
    ]);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(VerifikasiNasabah::class)
        ->assertSee('15/05/1990')
        ->assertSee('perempuan')
        ->assertSee('Pedagang');
});
