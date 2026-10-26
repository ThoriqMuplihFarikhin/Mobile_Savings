<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Livewire\Nasabah\Komplain;
use App\Livewire\Nasabah\PilihPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

/**
 * Kontras bertahap #1: pesan galat validasi portal nasabah sebelumnya hanya
 * `text-danger` (#B23A2E ≈ 3,0:1 di atas kartu gelap #18181b — di bawah
 * syarat WCAG 4,5:1 untuk teks normal). Varian `dark:text-dark-danger-text`
 * (#FCA5A5 ≈ 9,3:1 di kartu gelap, ≈ 10,4:1 di kanvas #0A0A0A) memakai token
 * DESIGN.md §2 yang sudah ada untuk pasangan danger gelap — tanpa token baru.
 * Warna terang tidak berubah (#B23A2E ≈ 5,9:1 di putih, lolos).
 *
 * Catatan: `@error('produk_id')` di ajukan-penarikan tidak pernah terender
 * karena submit() memvalidasi kunci `produkId` — markup mati pra-ada, tidak
 * disentuh di butir ini; tes memakai galat `nominal` yang tercapai.
 */
it('galat validasi ajukan penarikan memakai varian gelap teks danger', function () {
    $nasabah = User::factory()->nasabah()->create();

    $html = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)->call('submit')->html();

    expect(substr_count($html, 'text-danger dark:text-dark-danger-text'))->toBeGreaterThanOrEqual(1);
});

it('galat validasi pilih paket memakai varian gelap teks danger', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => uniqid('Paket Kontras '),
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $html = Livewire::actingAs($nasabah)
        ->test(PilihPaket::class)
        ->call('bukaKomitmen', $produk->id)
        ->call('ikutiPaket')
        ->html();

    expect(substr_count($html, 'text-danger dark:text-dark-danger-text'))->toBeGreaterThanOrEqual(2);
});

it('galat validasi komplain memakai varian gelap teks danger', function () {
    $nasabah = User::factory()->nasabah()->create();

    $html = Livewire::actingAs($nasabah)
        ->test(Komplain::class)
        ->call('toggleForm')
        ->call('submit')
        ->html();

    expect(substr_count($html, 'text-danger dark:text-dark-danger-text'))->toBeGreaterThanOrEqual(1);
});
