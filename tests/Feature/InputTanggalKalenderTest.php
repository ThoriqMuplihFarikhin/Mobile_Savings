<?php

use Illuminate\Support\Facades\Blade;

/**
 * D22 (P4): semua input tanggal harus memakai komponen x-ui.tanggal
 * (flatpickr), bukan picker bawaan browser.
 */
it('tidak ada input tanggal native browser di luar komponen x-ui.tanggal', function () {
    $dilarang = ['type="date"', 'type="month"', 'datetime-local'];

    $pelanggaran = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
    foreach ($files as $file) {
        if ($file->isDir() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        $rel = str_replace(str_replace('\\', '/', resource_path()).'/', '', str_replace('\\', '/', $file->getPathname()));
        $isi = (string) file_get_contents($file->getPathname());

        if ($rel === 'views/components/ui/tanggal.blade.php') {
            continue;
        }

        foreach ($dilarang as $kata) {
            if (str_contains($isi, $kata)) {
                $pelanggaran[] = $rel.' ('.$kata.')';
            }
        }
    }

    expect($pelanggaran)->toBeEmpty(
        'Input tanggal native ditemukan, ganti dengan <x-ui.tanggal>: '
        .implode('; ', $pelanggaran),
    );
});

it('komponen x-ui.tanggal merender atribut picker dan nilai server', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.tanggal wire:model.live="tanggal" nilai="2026-01-15" label="Tanggal Lahir" wajib min="2020-01-01" class="kelas-uji" />
        BLADE);

    expect($html)
        ->toContain('wire:ignore')
        ->toContain('value="2026-01-15"')
        ->toContain('data-tanggal-picker')
        ->toContain('x-data="pickerTanggal(')
        ->toContain('class="kelas-uji"')
        ->toContain('Tanggal Lahir')
        ->toContain('placeholder="Pilih tanggal"')
        ->toContain('for="tanggal-');
});

it('komponen x-ui.tanggal mode bulan memakai placeholder bulan', function () {
    $html = Blade::render(<<<'BLADE'
        <x-ui.tanggal mode="bulan" wire:model="bulan" />
        BLADE);

    expect($html)->toContain('placeholder="Pilih bulan"');
});

it('komponen x-ui.tanggal menolak mode yang tidak dikenal', function () {
    try {
        Blade::render(<<<'BLADE'
            <x-ui.tanggal mode="waktu" wire:model="x" />
            BLADE);

        $this->fail('Seharusnya melempar exception mode tidak dikenal');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Mode tanggal tidak dikenal: waktu');
    }
});
