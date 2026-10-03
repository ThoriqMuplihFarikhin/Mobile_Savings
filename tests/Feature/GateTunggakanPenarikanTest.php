<?php

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Carbon\Carbon;

it('blocks penarikan paket when tunggakan > 0 and tanggal sudah lewat', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->subDays(5)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'total_seharusnya_terkumpul' => 150000,
        'total_aktual_terkumpul' => 100000,
        'tunggakan' => 10,
        'status_alert' => 'peringatan',
    ]);

    $action = new AjukanPenarikanAction;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('tunggakan');

    $action->execute($nasabah, $produk, 50000, 'online');
});

it('allows penarikan paket when tunggakan = 0 and tanggal sudah lewat', function () {
    $now = now()->startOfDay();
    Carbon::setTestNow($now);

    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->subDays(5)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    $tanggalMulai = now()->subDays(30);
    $hariBerjalan = 31;
    $seharusnya = $hariBerjalan * 5000;

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => $tanggalMulai->toDateString(),
        'total_seharusnya_terkumpul' => $seharusnya,
        'total_aktual_terkumpul' => $seharusnya,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaan->id,
        'nominal' => $seharusnya,
        'status' => 'tercatat',
        'tanggal_transaksi' => $tanggalMulai->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
    ]);

    $action = new AjukanPenarikanAction;
    $penarikan = $action->execute($nasabah, $produk, 50000, 'online');

    Carbon::setTestNow();

    expect($penarikan)->toBeInstanceOf(TransaksiPenarikan::class);
    expect($penarikan->status)->toBe('pending');
});

it('blocks penarikan with tanggal message when tanggal belum lewat regardless of tunggakan', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->addDays(10)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
        'total_seharusnya_terkumpul' => 25000,
        'total_aktual_terkumpul' => 10000,
        'tunggakan' => 3,
        'status_alert' => 'peringatan',
    ]);

    $action = new AjukanPenarikanAction;

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('belum bisa dilakukan sebelum tanggal');

    $action->execute($nasabah, $produk, 50000, 'online');
});

it('bypasses tunggakan gate when keputusan_akhir is set by admin', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->subDays(5)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'total_seharusnya_terkumpul' => 150000,
        'total_aktual_terkumpul' => 100000,
        'tunggakan' => 10,
        'status_alert' => 'peringatan',
        'keputusan_akhir' => 'lanjut',
    ]);

    $action = new AjukanPenarikanAction;
    $penarikan = $action->execute($nasabah, $produk, 50000, 'online');

    expect($penarikan)->toBeInstanceOf(TransaksiPenarikan::class);
    expect($penarikan->status)->toBe('pending');
});

it('does not apply tunggakan gate for bebas products', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    $action = new AjukanPenarikanAction;
    $penarikan = $action->execute($nasabah, $produk, 50000, 'online');

    expect($penarikan)->toBeInstanceOf(TransaksiPenarikan::class);
    expect($penarikan->status)->toBe('pending');
});

it('does not write to database when checking tunggakan (read-only)', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->subDays(5)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'total_seharusnya_terkumpul' => 150000,
        'total_aktual_terkumpul' => 100000,
        'tunggakan' => 10,
        'status_alert' => 'peringatan',
    ]);

    $originalUpdatedAt = $kepesertaan->updated_at->copy();

    $action = new AjukanPenarikanAction;

    try {
        $action->execute($nasabah, $produk, 50000, 'online');
    } catch (Exception $e) {
        // Expected to throw
    }

    $kepesertaan->refresh();
    expect($kepesertaan->updated_at->eq($originalUpdatedAt))->toBeTrue();
});
