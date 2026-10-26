<?php

/**
 * Kontras bertahap #2: token --color-surface dan --color-surface-alt di blok
 * .dark dulu memakai navy (#131C2E, #0B1220) yang dilarang DESIGN.md
 * ("Permukaan netral murni … Tidak ada lagi navy" + tabel konversi
 * navy → Netral dark-*). Keduanya kini memakai tangga netral: dark-surface
 * #171717 dan dark-canvas #0a0a0a. Nilai warna token tidak muncul di HTML
 * hasil render, jadi tes mengunci sumber resources/css/app.css (komentar
 * dikeluarkan dulu agar hex di dokumentasi tidak ikut terhitung).
 */
function blokDarkAppCss(): string
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
    $mulai = strpos($css, '.dark {');
    $panjang = strpos($css, "\n    }", $mulai) - $mulai;
    $blok = substr($css, $mulai, $panjang);

    return preg_replace('!/\*.*?\*/!s', '', $blok);
}

it('blok .dark tidak lagi memuat navy permukaan yang dilarang desain', function () {
    $blok = blokDarkAppCss();

    expect($blok)
        ->not->toContain('#131C2E')
        ->not->toContain('#0B1220');
});

it('permukaan gelap memakai tangga netral dark-surface dan dark-canvas', function () {
    $blok = blokDarkAppCss();

    expect($blok)
        ->toContain('--color-surface: #171717;')
        ->toContain('--color-surface-alt: #0a0a0a;');
});
