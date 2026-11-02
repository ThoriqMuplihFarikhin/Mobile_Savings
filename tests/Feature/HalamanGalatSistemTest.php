<?php

use App\Livewire\Kolektor\Pengaturan;
use App\Models\User;
use Livewire\Livewire;

it('halaman 404 menampilkan layout galat resmi sistem dengan token desain', function () {
    $this->get('/rute-tidak-ada-pasti-404-test')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan', false)
        ->assertSee('Kode 404', false)
        ->assertSee('Ke beranda', false);
});

it('view galat sistem 419, 500, dan 403 dapat dirender dengan konten yang konsisten', function () {
    $v419 = view('errors.419')->render();
    expect($v419)
        ->toContain('Sesi sudah berakhir', false)
        ->toContain('Kode 419', false)
        ->toContain('Masuk lagi', false);

    $v500 = view('errors.500')->render();
    expect($v500)
        ->toContain('Ada gangguan di server', false)
        ->toContain('Kode 500', false)
        ->toContain('Coba lagi', false);

    $v403 = view('errors.403')->render();
    expect($v403)
        ->toContain('Akses tidak diizinkan', false)
        ->toContain('Kode 403', false)
        ->toContain('Ke beranda', false);
});

it('layar pengaturan kolektor bersih tanpa tombol dan modal pratinjau sistem', function () {
    $kolektor = User::factory()->kolektor()->create();

    $html = Livewire::actingAs($kolektor)->test(Pengaturan::class)->html();

    expect($html)
        ->not->toContain('showSystemModal', false)
        ->not->toContain('Halaman sistem', false)
        ->not->toContain('activeSysTab', false);
});
