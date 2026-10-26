<?php

use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('streak mengabaikan setoran tanggal masa depan', function () {
    $user = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $user->id,
        'nama' => $user->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $user->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    foreach ([now()->subDay(), now(), now()->addDay()] as $tanggal) {
        TransaksiSetoran::create([
            'nasabah_id' => $user->id,
            'produk_id' => $produk->id,
            'nominal' => 10000,
            'tanggal_transaksi' => $tanggal->toDateString(),
            'tanggal_input_sistem' => $tanggal,
            'input_by' => $user->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
        ]);
    }

    $this->actingAs($user);

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('streak', 2);
});

test('dashboard admin memberi padanan teks pada grafik dan memakai teal untuk segmen paket', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    $bebas = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);
    $paket = ProdukTabungan::create([
        'nama' => 'Paket Hemat',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $bebas->id, 'saldo' => 100000]);
    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $paket->id, 'saldo' => 50000]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $bebas->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('aria-label="Grafik tren setoran 30 hari terakhir', false)
        ->assertSee('aria-label="Komposisi saldo seluruh nasabah', false)
        ->assertSee('class="text-tile-teal-fg"', false)
        ->assertSee('rounded-full bg-tile-teal-fg', false)
        ->assertDontSee('stroke="currentColor" class="text-primary"', false)
        ->assertDontSee('rounded-full bg-primary"', false);
});
