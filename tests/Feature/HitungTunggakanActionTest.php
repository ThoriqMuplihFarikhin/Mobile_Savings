<?php

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;

it('returns null for non-paket product', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'harga_per_hari' => null,
        'status' => 'aktif',
    ]);

    $result = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id);
    expect($result)->toBeNull();
});

it('returns null when no kepesertaan exists and simpan is false', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 20000,
        'status' => 'aktif',
    ]);

    $result = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: false);
    expect($result)->toBeNull();
});

it('calculates tunggakan correctly with simpan false', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 20000,
        'status' => 'aktif',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);

    $result = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: false);
    expect($result)->not->toBeNull();
    expect($result['hari'])->toBeGreaterThanOrEqual(5);
    expect($result['tunggakan'])->toBeGreaterThan(0);
});

it('creates kepesertaan and saves when simpan is true and no kepesertaan exists', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 20000,
        'status' => 'aktif',
    ]);

    $result = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    $this->assertDatabaseHas('kepesertaan_paket', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
    ]);
});

it('updates kepesertaan when simpan is true', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 20000,
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);

    app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    $kepesertaan->refresh();
    expect($kepesertaan->total_seharusnya_terkumpul)->toBeGreaterThan(0);
    expect($kepesertaan->status_alert)->toBe('peringatan');
});
