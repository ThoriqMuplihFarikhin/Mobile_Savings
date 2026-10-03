<?php

use App\Actions\Tabungan\HitungTunggakanAction;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Carbon\Carbon;

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
    expect($result['hari'])->toBeGreaterThanOrEqual(6);
    expect($result['tunggakan_hari'])->toBeGreaterThanOrEqual(6);
    expect($result['tunggakan'])->toBeGreaterThanOrEqual(120000);
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
    expect($kepesertaan->tunggakan)->toBeGreaterThanOrEqual(6);
});

afterEach(fn () => Carbon::setTestNow());

/**
 * Siapkan nasabah + produk paket + kepesertaan untuk tes tunggakan.
 *
 * @return array{0: User, 1: ProdukTabungan, 2: KepesertaanPaket}
 */
function siapkanKepesertaanTunggakan(array $produk = [], array $kepesertaan = []): array
{
    $nasabah = User::factory()->nasabah()->create();
    $dataProduk = array_merge([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ], $produk);
    $paket = ProdukTabungan::create($dataProduk);

    $k = KepesertaanPaket::create(array_merge([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $paket->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ], $kepesertaan));

    return [$nasabah, $paket, $k];
}

function setorTunggakanHari(User $nasabah, ProdukTabungan $produk, float $nominal): void
{
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => KepesertaanPaket::where('nasabah_id', $nasabah->id)
            ->where('produk_id', $produk->id)
            ->whereNull('keputusan_akhir')
            ->value('id'),
        'nominal' => $nominal,
        'status' => 'tercatat',
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
    ]);
}

it('menghitung tunggakan hari dalam bilangan bulat pada jam berapa pun', function (string $jam) {
    Carbon::setTestNow(Carbon::parse('2026-09-30 '.$jam));

    [$nasabah, $produk, $k] = siapkanKepesertaanTunggakan(
        kepesertaan: ['tanggal_mulai_ikut' => '2026-09-27'],
    );
    setorTunggakanHari($nasabah, $produk, 30000);

    $hasil = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    expect($hasil['tunggakan_hari'])->toBeInt()->toBe(1);
    expect($hasil['tunggakan'])->toEqual(10000);

    $k->refresh();
    expect((int) $k->tunggakan)->toBe(1);
    expect((float) $k->total_seharusnya_terkumpul)->toBe(40000.0);
    expect($k->status_alert)->toBe('peringatan');
})->with([
    'tengah malam' => '00:05:00',
    'siang' => '15:30:00',
    'malam' => '23:55:00',
]);

it('menghitung nol tunggakan ketika nasabah menabung lebih dari kebutuhan hari ini', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00'));

    [$nasabah, $produk, $k] = siapkanKepesertaanTunggakan(
        kepesertaan: ['tanggal_mulai_ikut' => '2026-09-27'],
    );
    setorTunggakanHari($nasabah, $produk, 40000);
    setorTunggakanHari($nasabah, $produk, 10000);

    $hasil = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    expect($hasil)->toBeNull();

    $k->refresh();
    expect((int) $k->tunggakan)->toBe(0);
    expect($k->status_alert)->toBe('normal');
});

it('membatasi hari berjalan pada total hari periode paket', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00'));

    [$nasabah, $produk, $k] = siapkanKepesertaanTunggakan(
        produk: ['periode_mulai' => '2026-08-01', 'periode_selesai' => '2026-08-21'],
        kepesertaan: ['tanggal_mulai_ikut' => '2026-08-01'],
    );

    expect($produk->totalHariPaket())->toBeInt()->toBe(21);

    app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    $k->refresh();
    expect((int) $k->tunggakan)->toBe(21);
    expect((float) $k->total_seharusnya_terkumpul)->toBe(210000.0);
});

it('menganggap tanggal mulai ikut di masa depan sebagai nol hari berjalan', function () {
    Carbon::setTestNow(Carbon::parse('2026-09-30 10:00:00'));

    [$nasabah, $produk, $k] = siapkanKepesertaanTunggakan(
        kepesertaan: ['tanggal_mulai_ikut' => '2026-10-05'],
    );

    $hasil = app(HitungTunggakanAction::class)->execute($nasabah->id, $produk->id, simpan: true);

    expect($hasil)->toBeNull();

    $k->refresh();
    expect((int) $k->tunggakan)->toBe(0);
    expect((float) $k->total_seharusnya_terkumpul)->toBe(0.0);
});
