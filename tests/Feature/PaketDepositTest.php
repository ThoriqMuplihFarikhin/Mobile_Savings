<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

function seedSetoranPaket(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Paket',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDay()->toDateString(),
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function setorPaket(User $nasabah, ProdukTabungan $produk, int $nominal)
{
    return Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', $nominal)
        ->set('tanggal_transaksi', now()->toDateString())
        ->set('sumber_input', 'real_time')
        ->call('submit');
}

it('mengizinkan setoran berulang untuk produk paket', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetoranPaket();

    $this->actingAs($kolektor);

    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();
    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();
    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();

    $this->assertDatabaseCount('transaksi_setoran', 3);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('30000.00');
});

it('hanya membuat satu kepesertaan aktif meski setoran paket berulang', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetoranPaket();

    $this->actingAs($kolektor);

    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();
    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();

    expect(KepesertaanPaket::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->whereNull('keputusan_akhir')->count())->toBe(1);
});

it('tetap menolak setoran paket di bawah minimal setor', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedSetoranPaket();

    $this->actingAs($kolektor);

    setorPaket($nasabah, $produk, 10000)->assertHasNoErrors();
    setorPaket($nasabah, $produk, 500)->assertHasErrors(['nominal']);

    $this->assertDatabaseCount('transaksi_setoran', 1);
});
