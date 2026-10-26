<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase E butir C: layar sukses pengajuan penarikan (DESIGN.md §7 "Layar
 * sukses"). Setelah PIN benar dan pengajuan tersimpan, halaman menampilkan
 * lingkaran centang 104px dengan konfeti statis, ringkasan nominal dalam
 * kartu, catatan hijau ke Notifikasi, dan dua tombol — menggantikan flash
 * banner untuk pengajuan (flash sukses tetap dipakai kartu pengajuan-aktif
 * untuk pembatalan D18).
 */
function layarSuksesPenarikanFixture(): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => uniqid('Produk Sukses '),
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 500000]);

    return [$nasabah, $produk];
}

it('pengajuan terkirim membuka layar sukses dan menyembunyikan formulir', function () {
    [$nasabah, $produk] = layarSuksesPenarikanFixture();

    $component = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->set('pin', '123456')
        ->call('konfirmasi')
        ->assertSet('tampilSukses', true)
        ->assertSet('tampilKonfirmasi', false)
        ->assertSee('Pengajuan terkirim')
        ->assertSee('bg-decor-gold')
        ->assertDontSee('-- Pilih produk tabungan --')
        ->assertDontSee('Lanjut konfirmasi');

    $this->assertDatabaseHas('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'status' => 'pending',
    ]);

    expect(session()->has('success'))->toBeFalse();
});

it('layar sukses merangkum nominal, komisi, dan lokasi pengambilan', function () {
    [$nasabah, $produk] = layarSuksesPenarikanFixture();

    Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 100000)
        ->set('lokasi_pengambilan', 'rumah_kolektor')
        ->call('submit')
        ->set('pin', '123456')
        ->call('konfirmasi')
        ->assertSet('suksesNominalDiminta', '100000.00')
        ->assertSet('suksesKomisi', '5000.00')
        ->assertSet('suksesDiterima', '95000.00')
        ->assertSet('suksesLokasi', 'rumah_kolektor')
        ->assertSee('Nominal diminta')
        ->assertSee('Total diterima')
        ->assertSee('Rumah kolektor')
        ->assertSee('data-test="rupiah"', false);
});

it('layar sukses menampilkan catatan Notifikasi dan dua tombol tujuan', function () {
    [$nasabah, $produk] = layarSuksesPenarikanFixture();

    Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->set('pin', '123456')
        ->call('konfirmasi')
        ->assertSee('menunggu persetujuan admin')
        ->assertSee('Notifikasi')
        ->assertSee('Lihat notifikasi')
        ->assertSee('Kembali ke beranda');
});
