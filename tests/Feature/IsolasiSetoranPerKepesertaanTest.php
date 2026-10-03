<?php

use App\Livewire\Kolektor\InputSetoran;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function buatProdukPaketIsolasi(): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => 'Paket Isolasi',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);
}

function buatKepesertaanIsolasi(User $nasabah, ProdukTabungan $produk, string $tanggalMulai, string $createdAt, ?string $keputusan = null): KepesertaanPaket
{
    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => $tanggalMulai,
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
        'keputusan_akhir' => $keputusan,
    ]);
    KepesertaanPaket::whereKey($kepesertaan->id)->update(['created_at' => $createdAt]);

    return $kepesertaan;
}

function buatSetoranIsolasi(User $nasabah, ProdukTabungan $produk, float $nominal, string $createdAt, ?int $kepesertaanId = null): TransaksiSetoran
{
    $setoran = TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaanId,
        'nominal' => $nominal,
        'tanggal_transaksi' => substr($createdAt, 0, 10),
        'tanggal_input_sistem' => $createdAt,
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
    TransaksiSetoran::whereKey($setoran->id)->update(['created_at' => $createdAt]);

    return $setoran;
}

function seedIsolasiSetoranPaket(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Isolasi',
        'alamat' => 'Jl. Isolasi No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = buatProdukPaketIsolasi();

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return compact('kolektor', 'nasabah', 'produk');
}

it('setoran lama tidak dihitung pada kepesertaan baru setelah kepesertaan pertama diambil keputusan', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = buatProdukPaketIsolasi();

    $k1 = buatKepesertaanIsolasi($nasabah, $produk, now()->subDays(30)->toDateString(), now()->subDays(30)->toDateTimeString(), 'gagal_dikembalikan');
    buatSetoranIsolasi($nasabah, $produk, 100000, now()->subDays(20)->toDateTimeString(), $k1->id);

    $k2 = buatKepesertaanIsolasi($nasabah, $produk, now()->toDateString(), now()->subDay()->toDateTimeString());
    buatSetoranIsolasi($nasabah, $produk, 30000, now()->toDateTimeString(), $k2->id);

    $k1->hitungUlangKepesertaan(true);
    $k2->hitungUlangKepesertaan(true);
    $k1->refresh();
    $k2->refresh();

    expect((float) $k1->total_aktual_terkumpul)->toBe(100000.0)
        ->and((float) $k2->total_aktual_terkumpul)->toBe(30000.0);
});

it('backfill mengaitkan semua setoran ke kepesertaan tunggal pasangan', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = buatProdukPaketIsolasi();
    $k = buatKepesertaanIsolasi($nasabah, $produk, now()->subDays(10)->toDateString(), now()->subDays(10)->toDateTimeString());

    $s1 = buatSetoranIsolasi($nasabah, $produk, 20000, now()->subDays(8)->toDateTimeString());
    $s2 = buatSetoranIsolasi($nasabah, $produk, 25000, now()->subDays(3)->toDateTimeString());

    $sisa = (new AddKepesertaanIdToTransaksiSetoranTable)->backfill();

    expect($s1->refresh()->kepesertaan_id)->toBe($k->id)
        ->and($s2->refresh()->kepesertaan_id)->toBe($k->id)
        ->and($sisa)->toBe(0);
});

it('backfill mengaitkan setoran ke kepesertaan terbaru yang sudah ada sebelum setoran', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = buatProdukPaketIsolasi();

    $k1 = buatKepesertaanIsolasi($nasabah, $produk, '2026-09-01', '2026-09-01 08:00:00');
    $k2 = buatKepesertaanIsolasi($nasabah, $produk, '2026-09-15', '2026-09-15 08:00:00');

    $s0 = buatSetoranIsolasi($nasabah, $produk, 10000, '2026-08-01 08:00:00');
    $s1 = buatSetoranIsolasi($nasabah, $produk, 20000, '2026-09-05 08:00:00');
    $s2 = buatSetoranIsolasi($nasabah, $produk, 30000, '2026-09-20 08:00:00');

    $sisa = (new AddKepesertaanIdToTransaksiSetoranTable)->backfill();

    expect($s0->refresh()->kepesertaan_id)->toBeNull()
        ->and($s1->refresh()->kepesertaan_id)->toBe($k1->id)
        ->and($s2->refresh()->kepesertaan_id)->toBe($k2->id)
        ->and($sisa)->toBe(1);
});

it('setoran paket dari InputSetoran ditandai dengan kepesertaan aktif', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedIsolasiSetoranPaket();

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', 10000)
        ->set('tanggal_transaksi', now()->toDateString())
        ->set('sumber_input', 'real_time')
        ->call('submit')
        ->assertHasNoErrors();

    $kepesertaan = KepesertaanPaket::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->whereNull('keputusan_akhir')
        ->firstOrFail();

    $this->assertDatabaseHas('transaksi_setoran', [
        'nasabah_id' => $nasabah->id,
        'kepesertaan_id' => $kepesertaan->id,
    ]);
});

it('setoran produk bebas tidak ditandai kepesertaan', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Bebas',
        'alamat' => 'Jl. Bebas No. 1',
        'jenis_kelamin' => 'perempuan',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'harga_per_hari' => null,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->set('produkId', $produk->id)
        ->set('nominal', 10000)
        ->set('tanggal_transaksi', now()->toDateString())
        ->set('sumber_input', 'real_time')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_setoran', [
        'nasabah_id' => $nasabah->id,
        'kepesertaan_id' => null,
    ]);
});
