<?php

use App\Livewire\Admin\ManajemenNasabah;
use App\Models\KepesertaanPaket;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

it('membuka modal popup edit nasabah dan mengisi datanya', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['no_hp' => '081234567890']);

    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Modal Test',
        'alamat' => 'Jl. Modal No. 10',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->assertSet('showForm', false)
        ->call('edit', $profil->id)
        ->assertSet('showForm', true)
        ->assertSet('editId', $nasabah->id)
        ->assertSet('nama', 'Nasabah Modal Test')
        ->assertSet('no_hp', '081234567890')
        ->assertSet('alamat', 'Jl. Modal No. 10')
        ->assertSee('Edit Nasabah')
        ->call('toggleForm')
        ->assertSet('showForm', false);
});

it('membuka modal popup pendaftaran paket dan mendaftarkan nasabah ke paket', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Paket Popup',
        'alamat' => 'Jl. Mawar No. 4',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran Popup',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 15000,
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(30)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(31)->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('bukaDetail', $nasabah->id)
        ->assertSet('detailNasabahId', fn ($val) => ! empty($val))
        ->call('bukaDaftarPaket', $nasabah->id)
        ->assertSet('tampilDaftarPaket', true)
        ->assertSee('Daftarkan ke Paket Tabungan')
        ->set('produkDaftarId', $produk->id)
        ->set('setujuDaftar', true)
        ->set('catatanDaftar', 'Komitmen setoran via kantor admin.')
        ->call('daftarkanKePaket')
        ->assertHasNoErrors()
        ->assertSet('tampilDaftarPaket', false);

    expect(KepesertaanPaket::where('nasabah_id', $nasabah->id)->where('produk_id', $produk->id)->exists())->toBeTrue();
});
