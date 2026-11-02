<?php

use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('layout mobile dan admin memuat komponen modal foto bukti', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="modal-foto-bukti-popup"', false)
        ->assertSee('buka-foto-bukti');

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('data-test="modal-foto-bukti-popup"', false)
        ->assertSee('buka-foto-bukti');
});

it('halaman progres paket memiliki trigger modal foto bukti untuk bukti serah terima', function () {
    Storage::fake('local');
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Qurban Sapi',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    $path = 'serah-terima/bukti-popup-test.jpg';
    Storage::disk('local')->put($path, 'isi-foto-bukti');

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'nominal_per_hari' => 10000,
        'status_serah_terima' => 'sudah_diterima',
        'diterima_oleh' => 'Budi Santoso',
        'tanggal_serah_terima' => now()->toDateString(),
        'bukti_foto_url' => $path,
    ]);

    $this->actingAs($nasabah)
        ->get(route('nasabah.progres-paket.index'))
        ->assertOk()
        ->assertSee('data-test="modal-foto-bukti-popup"', false)
        ->assertSee('buka-foto-bukti')
        ->assertSee(route('serah-terima.bukti', $kepesertaan));
});
