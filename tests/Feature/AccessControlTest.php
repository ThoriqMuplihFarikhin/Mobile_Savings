<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

it('rejects kolektor input setoran for nasabah not assigned to them', function () {
    $kolektor1 = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor1);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('nasabahId', '')
        ->assertSet('selectedNasabah', null);

    $this->assertDatabaseMissing('transaksi_setoran', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
    ]);
});

it('allows kolektor input setoran for assigned nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('tanggal_transaksi', now()->format('Y-m-d'))
        ->set('sumber_input', 'real_time')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_setoran', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
    ]);
});

it('rejects withdrawal when pending withdrawals exceed available saldo', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

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

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 80000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 4000,
        'nominal_diterima' => 76000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 30000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
    ]);
});

it('allows withdrawal when pending withdrawals still leave sufficient saldo', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

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

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1500,
        'nominal_diterima' => 28500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'status' => 'pending',
    ]);
});
