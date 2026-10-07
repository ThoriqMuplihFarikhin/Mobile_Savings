<?php

use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Setoran lunas agar gerbang pencairan D17 tidak menolak karena tunggakan.
 */
function countdownSetorLunas(KepesertaanPaket $kepesertaan, ProdukTabungan $produk, User $nasabah, float $nominal): void
{
    DB::table('transaksi_setoran')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaan->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
}

it('shows countdown as integer without decimal', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->addDays(15)->toDateString(),
        'status' => 'aktif',
    ]);

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'total_seharusnya_terkumpul' => 50000,
        'total_aktual_terkumpul' => 30000,
        'tunggakan' => 0,
        'status_alert' => 'normal',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);

    $this->actingAs($nasabah);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('15 Hari Lagi');
    $response->assertDontSee('15.0 Hari Lagi');
    $response->assertDontSee('15.41 Hari Lagi');
});

it('shows "Sudah Bisa Dicairkan" when tanggal cair has passed', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->subDays(3)->toDateString(),
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'total_seharusnya_terkumpul' => 150000,
        'total_aktual_terkumpul' => 150000,
        'tunggakan' => 0,
        'status_alert' => 'normal',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);

    countdownSetorLunas($kepesertaan, $produk, $nasabah, 200000);

    $this->actingAs($nasabah);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Sudah Bisa Dicairkan');
    $response->assertDontSee('Hari Lagi');
});

it('shows "Sudah Bisa Dicairkan" when tanggal cair is today', function () {
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'harga_per_hari' => 5000,
        'tanggal_boleh_cair' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(20)->toDateString(),
        'total_seharusnya_terkumpul' => 100000,
        'total_aktual_terkumpul' => 100000,
        'tunggakan' => 0,
        'status_alert' => 'normal',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);

    countdownSetorLunas($kepesertaan, $produk, $nasabah, 200000);

    $this->actingAs($nasabah);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertSee('Sudah Bisa Dicairkan');
    $response->assertDontSee('Hari Lagi');
});
