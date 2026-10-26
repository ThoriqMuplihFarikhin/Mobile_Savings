<?php

it('menyediakan halaman offline statis tanpa autentikasi', function () {
    $this->get('/offline')
        ->assertOk()
        ->assertSee('Koneksi Terputus', false)
        ->assertSee('Coba Lagi', false)
        ->assertDontSee('/build/')
        ->assertDontSee('flux.js')
        ->assertDontSee('fonts.googleapis');
});

it('service worker menampilkan halaman offline saat navigasi gagal', function () {
    $sw = file_get_contents(public_path('service-worker.js'));

    expect($sw)->toContain("permintaan.mode === 'navigate'");
    expect($sw)->toContain('HALAMAN_OFFLINE');
    expect($sw)->toContain("'/offline'");

    preg_match('/permintaan\.mode === .navigate.[\s\S]*?return;\s*\}/', $sw, $cabangNavigasi);
    expect($cabangNavigasi)->not->toBeEmpty();
    expect($cabangNavigasi[0])->not->toContain('cache.put');
});
