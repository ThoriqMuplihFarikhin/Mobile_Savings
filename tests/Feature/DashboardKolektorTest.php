<?php

use App\Models\JadwalKunjungan;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;

function seedKolektorDashboard(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    JadwalKunjungan::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_jadwal' => now()->toDateString(),
        'status_kunjungan' => null,
    ]);

    return compact('kolektor', 'nasabah', 'produk');
}

it('menampilkan satu baris jadwal walau nasabah punya banyak kepesertaan', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKolektorDashboard();

    $produkBonus = ProdukTabungan::create([
        'nama' => 'Paket Bonus',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 1000,
        'status' => 'aktif',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(60)->toDateString(),
        'tunggakan' => 99000,
        'status_alert' => 'peringatan',
        'keputusan_akhir' => 'lanjut',
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'tunggakan' => 20000,
        'status_alert' => 'peringatan',
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produkBonus->id,
        'tanggal_mulai_ikut' => now()->subDays(5)->toDateString(),
        'tunggakan' => 50000,
        'status_alert' => 'normal',
    ]);

    $this->actingAs($kolektor);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $jadwal = $response->viewData('jadwalHariIni');

    expect($jadwal)->toHaveCount(1)
        ->and($jadwal->first()->nasabah_name)->toBe($nasabah->name)
        ->and($jadwal->first()->tunggakan)->toBe('50000.00')
        ->and($jadwal->first()->status_alert)->toBe('normal')
        ->and($jadwal->first()->produk_name)->toBe('Paket Bonus')
        ->and($response->viewData('stats')['kunjungan_total'])->toBe(1);
});

it('tidak menghitung kepesertaan selesai sebagai tunggakan parah', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKolektorDashboard();

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(60)->toDateString(),
        'tunggakan' => 50000,
        'status_alert' => 'peringatan',
        'keputusan_akhir' => 'lanjut',
    ]);

    $this->actingAs($kolektor);
    $response = $this->get(route('dashboard'));

    expect($response->viewData('nasabahTunggakParah'))->toBe(0);
});

it('menjumlahkan setoran hari ini saja di dashboard admin', function () {
    $admin = User::factory()->admin()->create();
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKolektorDashboard();

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 30000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->subDay()->toDateString(),
        'tanggal_input_sistem' => now()->subDay(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => 'tercatat',
    ]);
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 70000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'dibatalkan',
    ]);

    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    expect((float) $response->viewData('setoranHariIni'))->toBe(30000.0);
});
