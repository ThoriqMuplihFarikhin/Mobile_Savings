<?php

use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;

it('halaman saldo mobile memakai kartu saldo hero, intip saldo, dan aksi tarik produk', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produkBebas = ProdukTabungan::create([
        'nama' => 'Tabungan Sukarela',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $produkPaket = ProdukTabungan::create([
        'nama' => 'Paket Lebaran 2026',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'total_hari' => 100,
        'tanggal_boleh_cair' => now()->addMonths(2)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produkBebas->id,
        'saldo' => 500000,
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produkPaket->id,
        'saldo' => 300000,
    ]);

    $response = $this->actingAs($nasabah)->get(route('nasabah.saldo.index'));

    $response->assertOk()
        ->assertSee('Saldo saya')
        ->assertSee('Total saldo')
        ->assertSee('data-test="kartu-saldo-hero"', false)
        ->assertSee('data-test="tombol-intip-saldo"', false)
        ->assertSee('data-test="kartu-produk-saldo"', false)
        ->assertSee('data-test="tombol-tarik-produk"', false)
        ->assertSee('data-test="rupiah"', false)
        ->assertSee('Tabungan Sukarela')
        ->assertSee('Paket Lebaran 2026')
        ->assertSee('Bebas')
        ->assertSee('Paket')
        ->assertDontSee('text-rose');
});

it('halaman saldo menampilkan empty state tunas bila belum ada tabungan', function () {
    $nasabah = User::factory()->nasabah()->create();

    $response = $this->actingAs($nasabah)->get(route('nasabah.saldo.index'));

    $response->assertOk()
        ->assertSee('Belum ada tabungan aktif')
        ->assertSee('Hubungi kolektor Anda untuk mulai menabung.');
});
