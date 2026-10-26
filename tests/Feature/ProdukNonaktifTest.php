<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

function buatProdukT46(string $status): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => 'Produk '.$status,
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => $status,
    ]);
}

it('ajukan penarikan tetap menampilkan saldo produk nonaktif dan menawarkan produk nonaktif bersaldo', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produkAktif = buatProdukT46('aktif');
    $produkNonaktif = buatProdukT46('nonaktif');
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produkNonaktif->id, 'saldo' => 75000]);
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produkAktif->id, 'saldo' => 50000]);

    $this->actingAs($nasabah);

    $component = Livewire::test(AjukanPenarikan::class);

    $saldoProdukIds = $component->viewData('saldoList')->pluck('produk_id');
    expect($saldoProdukIds->toArray())->toContain($produkNonaktif->id);

    $dropdownProdukIds = $component->viewData('produkList')->pluck('id');
    expect($dropdownProdukIds->toArray())
        ->toContain($produkAktif->id)
        ->toContain($produkNonaktif->id);
});

it('input setoran tidak otomatis memilih saldo produk nonaktif', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produkNonaktif = buatProdukT46('nonaktif');
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produkNonaktif->id, 'saldo' => 75000]);
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->call('pilihNasabah', $nasabah->id)
        ->assertSet('produkId', '');
});

it('input setoran menolak produk nonaktif saat submit', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produkNonaktif = buatProdukT46('nonaktif');
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produkNonaktif->id)
        ->set('nominal', 50000)
        ->set('tanggal_transaksi', now()->format('Y-m-d'))
        ->set('sumber_input', 'real_time')
        ->call('submit')
        ->assertHasErrors(['produkId']);

    $this->assertDatabaseMissing('transaksi_setoran', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produkNonaktif->id,
    ]);
});
