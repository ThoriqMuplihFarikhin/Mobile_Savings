<?php

use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
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
