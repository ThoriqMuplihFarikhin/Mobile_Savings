<?php

test('halaman login nasabah memuat form dengan field yang benar dan komponen baru', function () {
    $response = $this->get(route('login'));

    $response->assertOk()
        ->assertSee('value="nasabah"', false)
        ->assertSee('name="no_hp"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="remember"', false)
        ->assertSee('data-test="label-portal"', false)
        ->assertSee('Portal Nasabah')
        ->assertSee('data-test="login-button"', false)
        ->assertSee('Masuk')
        ->assertSee('auth-jar-fill', false)
        ->assertSee('auth-coin-1', false)
        ->assertSee('Saya Nasabah')
        ->assertSee('Saya Kolektor');
});

test('halaman login kolektor memuat form dengan field yang benar dan aksen kolektor', function () {
    $response = $this->get(route('login.kolektor'));

    $response->assertOk()
        ->assertSee('value="kolektor"', false)
        ->assertSee('name="no_hp"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="remember"', false)
        ->assertSee('data-test="label-portal"', false)
        ->assertSee('Portal Kolektor')
        ->assertSee('data-test="login-button"', false)
        ->assertSee('auth-jar-fill', false)
        ->assertSee('Saya Nasabah')
        ->assertSee('Saya Kolektor');
});

test('portal aktif pada pemilih portal tidak menautkan dirinya sendiri', function () {
    // Pada portal Nasabah: 'Saya Nasabah' adalah span, 'Saya Kolektor' adalah tautan
    $nasabah = $this->get(route('login'))->assertOk();
    $nasabahContent = $nasabah->getContent();

    expect($nasabahContent)->toContain('href="'.route('login.kolektor').'"')
        ->and($nasabahContent)->toContain('Saya Nasabah</span>');

    // Pada portal Kolektor: 'Saya Kolektor' adalah span, 'Saya Nasabah' adalah tautan
    $kolektor = $this->get(route('login.kolektor'))->assertOk();
    $kolektorContent = $kolektor->getContent();

    expect($kolektorContent)->toContain('href="'.route('login').'"')
        ->and($kolektorContent)->toContain('Saya Kolektor</span>');
});

test('pesan locked dan idle_expired tampil pada halaman login nasabah dan kolektor', function () {
    $this->withSession(['locked' => true])
        ->get(route('login'))
        ->assertOk()
        ->assertSee('Akun terkunci karena terlalu banyak percobaan gagal');

    $this->withSession(['idle_expired' => true])
        ->get(route('login.kolektor'))
        ->assertOk()
        ->assertSee('Sesi berakhir karena tidak aktif');
});

test('portal admin tidak memuat komponen desain baru dan mempertahankan tampilan padat', function () {
    $response = $this->get(route('login.admin'));

    $response->assertOk()
        ->assertSee('value="admin"', false)
        ->assertSee('data-test="label-portal"', false)
        ->assertSee('Portal Admin')
        ->assertSee('data-test="login-button"', false)
        ->assertSee('data-test="tautan-portal-lain"', false)
        ->assertDontSee('auth-jar-fill', false)
        ->assertDontSee('auth-coin-1', false)
        ->assertDontSee('Saya Nasabah')
        ->assertDontSee('Saya Kolektor')
        ->assertDontSee('min-[900px]:grid-cols-2', false);
});
