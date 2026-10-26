<?php

use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §2 "Ubin kategori": pasangan latar/ikon hanya dari tabel pemetaan,
 * radius 14px (§6 token `tile`), dan ukuran 40/44/56px. Ubin bukan status, jadi
 * komponen ini tidak memuat teks — label ditulis di view pemakai.
 */
$kategoriUbin = [
    'hijau' => ['bg-primary-soft', 'text-on-primary-soft'],
    'biru' => ['bg-info-soft', 'text-info'],
    'teal' => ['bg-tile-teal-bg', 'text-tile-teal-fg'],
    'kuning' => ['bg-warning-soft', 'text-warning-deep'],
    'netral' => ['bg-inset', 'text-body'],
    'mawar' => ['bg-danger-soft', 'text-danger-deep'],
];

$kasusUbin = [];
foreach ($kategoriUbin as $kategori => [$latar, $ikon]) {
    $kasusUbin[$kategori] = [$kategori, $latar, $ikon];
}

it('memetakan tiap kategori ke pasangan warna tabel DESIGN.md §2', function (string $kategori, string $latar, string $ikon) {
    $html = Blade::render('<x-ui.tile :kategori="$kategori"><span>isi-ikon</span></x-ui.tile>', [
        'kategori' => $kategori,
    ]);

    expect($html)
        ->toContain('data-test="tile"')
        ->toContain($latar)
        ->toContain($ikon)
        ->toContain('rounded-[14px]')
        ->toContain('isi-ikon');
})->with($kasusUbin);

it('memakai pasangan gelap tersendiri untuk ubin mawar', function () {
    $html = Blade::render('<x-ui.tile kategori="mawar" />');

    expect($html)
        ->toContain('dark:bg-dark-danger-soft')
        ->toContain('dark:text-dark-danger-text');
});

it('mengikuti ukuran 40, 44, dan 56 pixel', function (int $ukuran, string $kelas) {
    $html = Blade::render('<x-ui.tile :ukuran="'.$ukuran.'" />');

    expect($html)->toContain($kelas);
})->with([
    '40 pixel' => [40, 'size-10'],
    '44 pixel' => [44, 'size-11'],
    '56 pixel' => [56, 'size-14'],
]);

it('menolak kategori yang tidak dikenal', function () {
    try {
        Blade::render('<x-ui.tile kategori="ungu" />');

        $this->fail('Seharusnya melempar exception kategori tidak dikenal');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Kategori ubin tidak dikenal: ungu');
    }
});

it('menolak ukuran yang tidak dikenal', function () {
    try {
        Blade::render('<x-ui.tile :ukuran="100" />');

        $this->fail('Seharusnya melempar exception ukuran tidak dikenal');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Ukuran ubin tidak dikenal: 100');
    }
});
