<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;

/**
 * Temuan K1: blok @error('produk_id') pada ajukan-penarikan tidak pernah
 * tampil karena komponen memvalidasi 'produkId' (camelCase, sama dengan
 * properti Livewire). Kunci view disamakan dan pesan wajib produk kini
 * terlihat di bawah pemilih produk.
 */
it('pesan galat produk tampil setelah submit tanpa memilih produk', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Produk Galat Penarikan',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 100000]);

    $komponen = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->call('submit')
        ->assertHasErrors(['produkId' => 'required']);

    $pesanProduk = Validator::make([], ['produkId' => 'required'])->errors()->first('produkId');

    $komponen->assertSee($pesanProduk, false);
});

it('view penarikan memakai kunci @error produkId, bukan produk_id', function () {
    $sumber = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/nasabah/ajukan-penarikan.blade.php');

    expect($sumber)
        ->toContain('@error(\'produkId\')', false)
        ->not->toContain('@error(\'produk_id\')', false);
});
