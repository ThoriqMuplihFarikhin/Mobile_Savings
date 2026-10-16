<?php

use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §1 "Dekorasi" dan §10: ilustrasi datar tanpa gradien, selalu
 * aria-hidden karena tidak membawa informasi, dan maksimum satu per layar.
 */
it('merender ilustrasi tunas yang disembunyikan dari pembaca layar', function () {
    $html = Blade::render('<x-ui.ilustrasi nama="tunas" />');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('focusable="false"')
        ->toContain('width="96"')
        ->toContain('height="96"')
        ->toContain('class="fill-primary"')
        ->not->toContain('role="img"');
});

it('merender ilustrasi kado dengan warna ubin teal', function () {
    $html = Blade::render('<x-ui.ilustrasi nama="kado" />');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('fill-tile-teal-fg')
        ->toContain('fill-tile-teal-bg');
});

it('merender dekor hero berisi lingkaran, koin Rp, dan tunas', function () {
    $html = Blade::render('<x-ui.ilustrasi nama="dekor-hero" />');

    expect($html)
        ->toContain('aria-hidden="true"')
        ->toContain('fill-decor-gold')
        ->toContain('fill-decor-green')
        ->toContain('fill-on-hero-mute')
        ->toContain('>Rp<');
});

it('mengikuti ukuran yang diminta', function () {
    $html = Blade::render('<x-ui.ilustrasi nama="tunas" :ukuran="64" />');

    expect($html)
        ->toContain('width="64"')
        ->toContain('height="64"');
});

it('menolak nama ilustrasi yang tidak dikenal', function () {
    try {
        Blade::render('<x-ui.ilustrasi nama="beruang" />');

        $this->fail('Seharusnya melempar exception nama ilustrasi tidak dikenal');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Ilustrasi tidak dikenal: beruang');
    }
});
