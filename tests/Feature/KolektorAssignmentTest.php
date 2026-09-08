<?php

use App\Livewire\Admin\KelolaKolektor;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

it('allows admin to assign nasabah to kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
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

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', $kolektor->id)
        ->set('assignNasabahId', $nasabah->id)
        ->call('assignNasabah')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('kolektor_nasabah', [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'status' => 'aktif',
    ]);
});

it('allows admin to remove nasabah assignment', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $assignment = KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleAssign', $kolektor->id)
        ->call('removeAssign', $assignment->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('kolektor_nasabah', [
        'id' => $assignment->id,
        'status' => 'nonaktif',
    ]);
});

it('prevents kolektor deactivation when unsettled cash exists', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleStatus', $kolektor->id);

    $this->assertDatabaseHas('users', [
        'id' => $kolektor->id,
        'status_akun' => 'aktif',
    ]);
});

it('allows kolektor deactivation when no unsettled cash', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create(['status_akun' => 'aktif']);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleStatus', $kolektor->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('users', [
        'id' => $kolektor->id,
        'status_akun' => 'terkunci',
    ]);
});
