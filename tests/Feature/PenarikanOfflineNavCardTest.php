<?php

use App\Livewire\Kolektor\PenarikanOffline;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

it('shows navigation card with correct link on penarikan offline page', function () {
    $kolektor = User::factory()->kolektor()->create();

    NasabahProfil::create([
        'user_id' => $kolektor->id,
        'nama' => 'Kolektor Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->assertSee('Verifikasi penarikan')
        ->assertSee(route('kolektor.verifikasi-penarikan.index'));
});

it('shows badge with correct count of pending verifications', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    // Create 2 approved offline withdrawals for rumah_kolektor
    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1500,
        'nominal_diterima' => 28500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->assertSee('2')
        ->assertSet('jumlahMenungguVerifikasi', 2);
});

it('hides badge when no pending verifications', function () {
    $kolektor = User::factory()->kolektor()->create();

    NasabahProfil::create([
        'user_id' => $kolektor->id,
        'nama' => 'Kolektor Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->assertSet('jumlahMenungguVerifikasi', 0)
        ->assertDontSee('rounded-full bg-emerald-600');
});

it('prevents non-kolektor from accessing penarikan offline page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $response = $this->get('/kolektor/penarikan-offline');

    $response->assertStatus(403);
});
