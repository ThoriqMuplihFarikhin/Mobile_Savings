<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

it('allows kolektor to input deposit and updates saldo', function () {
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
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
        'input_by' => $kolektor->id,
        'status' => 'tercatat',
    ]);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('50000.00');
});

it('rejects deposit with nominal below minimum', function () {
    $kolektor = User::factory()->kolektor()->create();
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', 500)
        ->set('tanggal_transaksi', now()->format('Y-m-d'))
        ->set('sumber_input', 'real_time')
        ->call('submit')
        ->assertHasErrors(['nominal']);
});

it('accumulates saldo across multiple deposits', function () {
    $kolektor = User::factory()->kolektor()->create();
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
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

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', 30000)
        ->set('tanggal_transaksi', now()->format('Y-m-d'))
        ->set('sumber_input', 'susulan')
        ->call('submit')
        ->assertHasNoErrors();

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('80000.00');

    $this->assertDatabaseCount('transaksi_setoran', 2);
});
