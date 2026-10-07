<?php

use App\Livewire\Admin\SerahTerimaPaket;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

it('input foto bukti memakai accept image dan capture agar kamera terbuka di webview', function () {
    $this->actingAs(User::factory()->admin()->create());

    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P83',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'tanggal_boleh_cair' => now()->toDateString(),
        'status' => 'aktif',
    ]);
    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'metode_pengambilan' => 'ambil_sendiri',
        'status_serah_terima' => 'belum',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);
    DB::table('transaksi_setoran')->insert([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaan->id,
        'nominal' => 10000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::test(SerahTerimaPaket::class)
        ->call('bukaKonfirmasi', $kepesertaan->id)
        ->assertSee('accept="image/*" capture', false);
});

it('halaman absen memakai api web standar untuk lokasi dan kamera', function () {
    $this->actingAs(User::factory()->kolektor()->create());

    $this->get(route('kolektor.absen.index'))
        ->assertOk()
        ->assertSee('navigator.geolocation')
        ->assertSee('getUserMedia');
});
