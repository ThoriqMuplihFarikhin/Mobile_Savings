<?php

use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §7 "Cincin progres, lencana, dan streak": track primary-soft, isi
 * primary, ujung membulat, persentase di tengah dengan ukuran teks ¼ diameter
 * dan bobot 600. §10 mewajibkan role="img" dan aria-label berisi persen.
 */
it('merender cincin dengan role="img", aria-label persen, dan track serta isi dari token', function () {
    $html = Blade::render('<x-ui.progress-ring :persen="42" />');

    expect($html)
        ->toContain('data-test="progress-ring"')
        ->toContain('role="img"')
        ->toContain('aria-label="42 persen"')
        ->toContain('class="stroke-primary-soft"')
        ->toContain('class="stroke-primary"')
        ->toContain('stroke-linecap="round"')
        ->toContain('42%');
});

it('memakai ukuran teks seperempat diameter dengan bobot 600', function (int $ukuran, int $ukuranTeks) {
    $html = Blade::render('<x-ui.progress-ring :persen="50" :ukuran="'.$ukuran.'" />');

    expect($html)
        ->toContain('font-size: '.$ukuranTeks.'px')
        ->toContain('font-semibold')
        ->toContain('tabular-nums');
})->with([
    'lencana barang 56' => [56, 14],
    'kartu beranda 88' => [88, 22],
    'halaman progres 132' => [132, 33],
]);

it('membatasi persen antara 0 dan 100', function () {
    $lebih = Blade::render('<x-ui.progress-ring :persen="150" />');
    $kurang = Blade::render('<x-ui.progress-ring :persen="-10" />');

    expect($lebih)
        ->toContain('aria-label="100 persen"')
        ->toContain('>100%</span>')
        ->and($kurang)
        ->toContain('aria-label="0 persen"')
        ->toContain('>0%</span>');
});

it('memakai label khusus bila diberikan', function () {
    $html = Blade::render('<x-ui.progress-ring :persen="42" label="Capaian paket Sari" />');

    expect($html)->toContain('aria-label="Capaian paket Sari"');
});

it('menyembunyikan angka di tengah dari pembaca layar', function () {
    $html = Blade::render('<x-ui.progress-ring :persen="42" />');

    expect($html)->toContain('aria-hidden="true" class="absolute');
});

it('menampilkan centang, bukan persentase, untuk lencana yang tercapai', function () {
    $tercapai = Blade::render('<x-ui.progress-ring :persen="100" :centang="true" label="Beras, 5 kg" />');
    $tanpaLabel = Blade::render('<x-ui.progress-ring :persen="100" :centang="true" />');

    expect($tercapai)
        ->toContain('data-test="progress-ring"')
        ->toContain('aria-label="Beras, 5 kg"')
        ->toContain('stroke-linejoin="round"')
        ->not->toContain('100%')
        ->and($tanpaLabel)
        ->toContain('aria-label="Tercapai"');
});
