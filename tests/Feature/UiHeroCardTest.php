<?php

use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §7 "Kartu, hero, dan catatan": hero-card berlatar primary-deep,
 * radius 24px (token `hero`), padding 20px, dan teks on-hero. Dekor datar di
 * pojok kanan atas aria-hidden serta selalu berada di bawah lapisan isi (§13:
 * jangan menaruh teks di atas koin atau lingkaran dekor hero).
 */
it('merender hero-card dengan latar hijau tua, radius 24px, dan teks on-hero', function () {
    $html = Blade::render('<x-ui.hero-card><p>Total saldo Anda</p></x-ui.hero-card>');

    expect($html)
        ->toContain('data-test="hero-card"')
        ->toContain('bg-primary-deep')
        ->toContain('rounded-3xl')
        ->toContain('p-5')
        ->toContain('text-on-hero')
        ->toContain('Total saldo Anda');
});

it('menaruh dekor aria-hidden di bawah lapisan isi', function () {
    $html = Blade::render('<x-ui.hero-card><span>Isi hero</span></x-ui.hero-card>');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('z-0')
        ->toContain('relative z-10')
        ->toContain('>Rp<')
        ->toContain('overflow-hidden');
});

it('meneruskan atribut tambahan ke elemen hero', function () {
    $html = Blade::render('<x-ui.hero-card class="mt-4" />');

    expect($html)->toContain('mt-4');
});
