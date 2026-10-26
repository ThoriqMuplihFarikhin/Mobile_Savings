<?php

use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\ManajemenNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

function adminNullSafetyP44(): User
{
    return User::factory()->admin()->create();
}

it('edit nasabah dengan id tidak valid tidak membuat error', function () {
    $admin = adminNullSafetyP44();

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('edit', 999999)
        ->assertSet('showForm', false)
        ->assertSee('tidak ditemukan');
});

it('save nasabah dengan editId yang sudah tidak ada tidak membuat user baru', function () {
    $admin = adminNullSafetyP44();

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->set('editId', 999999)
        ->set('nama', 'Nasabah Hilang')
        ->set('no_hp', '081234567893')
        ->set('alamat', 'Jl. Hilang No. 1')
        ->call('save')
        ->assertSee('tidak ditemukan');

    expect(User::where('no_hp', '081234567893')->exists())->toBeFalse();
});

it('edit kolektor dengan id tidak valid tidak membuat error', function () {
    $admin = adminNullSafetyP44();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('edit', 999999)
        ->assertSet('showForm', false)
        ->assertSee('tidak ditemukan');
});

it('save kolektor dengan editId yang sudah tidak ada tidak membuat user baru', function () {
    $admin = adminNullSafetyP44();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->set('editId', 999999)
        ->set('name', 'Kolektor Hilang')
        ->set('noHp', '081234567894')
        ->call('save')
        ->assertSee('tidak ditemukan');

    expect(User::where('no_hp', '081234567894')->exists())->toBeFalse();
});

it('toggle assign kolektor dengan id tidak valid tidak membuat error', function () {
    $admin = adminNullSafetyP44();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', 999999)
        ->assertSet('showAssign', false)
        ->assertSee('tidak ditemukan');
});

it('assign nasabah tanpa kolektor terpilih menampilkan pesan bukan error', function () {
    $admin = adminNullSafetyP44();
    $nasabah = NasabahProfil::create([
        'user_id' => User::factory()->nasabah()->create()->id,
        'nama' => 'Nasabah P44',
        'alamat' => 'Jl. P44 No. 1',
        'tanggal_lahir' => '1990-01-01',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->set('assignNasabahId', $nasabah->user_id)
        ->call('assignNasabah')
        ->assertSee('kolektor terlebih dahulu');
});
