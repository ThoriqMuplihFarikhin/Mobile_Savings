<?php

use App\Livewire\Admin\ManajemenNasabah;
use App\Models\JadwalKunjungan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function createNasabahWithProfil(User $admin): User
{
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return $nasabah;
}

function createSavingProduct(): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);
}

it('rejects deletion when nasabah still has balance', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = createNasabahWithProfil($admin);
    $profil = NasabahProfil::where('user_id', $nasabah->id)->firstOrFail();
    $produk = createSavingProduct();

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 50000,
    ]);

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete')
        ->assertSet('confirmDelete', true)
        ->assertSet('deleteId', $profil->id);

    $this->assertDatabaseHas('users', ['id' => $nasabah->id]);
    $this->assertDatabaseHas('nasabah_profil', ['id' => $profil->id]);
    $this->assertDatabaseHas('saldo_produk', ['nasabah_id' => $nasabah->id]);
});

it('rejects deletion when nasabah has deposits', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = createNasabahWithProfil($admin);
    $profil = NasabahProfil::where('user_id', $nasabah->id)->firstOrFail();
    $produk = createSavingProduct();

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete')
        ->assertSet('confirmDelete', true);

    $this->assertDatabaseHas('users', ['id' => $nasabah->id]);
    $this->assertDatabaseHas('nasabah_profil', ['id' => $profil->id]);
    $this->assertDatabaseCount('transaksi_setoran', 1);
});

it('rejects deletion when nasabah has withdrawals', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = createNasabahWithProfil($admin);
    $profil = NasabahProfil::where('user_id', $nasabah->id)->firstOrFail();
    $produk = createSavingProduct();

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete')
        ->assertSet('confirmDelete', true);

    $this->assertDatabaseHas('users', ['id' => $nasabah->id]);
    $this->assertDatabaseHas('nasabah_profil', ['id' => $profil->id]);
    $this->assertDatabaseCount('transaksi_penarikan', 1);
});

it('deletes clean nasabah with all related records', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = createNasabahWithProfil($admin);
    $profil = NasabahProfil::where('user_id', $nasabah->id)->firstOrFail();
    $produk = createSavingProduct();

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 0,
    ]);

    JadwalKunjungan::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_jadwal' => now()->toDateString(),
    ]);

    expect($nasabah->hasRole('nasabah'))->toBeTrue();

    $this->actingAs($admin);

    Livewire::test(ManajemenNasabah::class)
        ->call('confirmDelete', $profil->id)
        ->call('delete')
        ->assertSet('confirmDelete', false)
        ->assertSet('deleteId', null);

    $this->assertDatabaseMissing('users', ['id' => $nasabah->id]);
    $this->assertDatabaseMissing('nasabah_profil', ['id' => $profil->id]);
    $this->assertDatabaseMissing('kolektor_nasabah', ['nasabah_id' => $nasabah->id]);
    $this->assertDatabaseMissing('saldo_produk', ['nasabah_id' => $nasabah->id]);
    $this->assertDatabaseMissing('jadwal_kunjungan', ['nasabah_id' => $nasabah->id]);
    $this->assertDatabaseMissing('model_has_roles', [
        'model_id' => $nasabah->id,
        'model_type' => User::class,
    ]);
});
