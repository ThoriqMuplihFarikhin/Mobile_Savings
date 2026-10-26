<?php

use Illuminate\Support\Facades\Blade;

/**
 * DESIGN.md §7 "Penanda tahap (stepper)": titik 24px dihubungkan garis 2px dan
 * label 12px di bawahnya. Tahap tercapai = hijau + centang, belum = outline
 * tanpa ikon, ditolak = merah tanpa centang dengan label tetap tertulis (§10:
 * penanda tahap tidak hanya mengandalkan warna).
 */

/**
 * Ambil isi titik berdasarkan statusnya untuk membandingkan ikon di dalamnya.
 */
function isiTitikStepper(string $html, string $status): string
{
    preg_match('/data-tahap="'.$status.'"[^>]*>(.*?)<\/span>/s', $html, $cocok);

    return $cocok[1] ?? '';
}

it('menandai tahap tercapai dengan hijau, centang, dan garis utuh', function () {
    $html = Blade::render('<x-ui.stepper :tahap="$tahap" :saat-ini="1" />', [
        'tahap' => ['Diajukan', 'Disetujui', 'Selesai'],
    ]);

    expect($html)
        ->toContain('data-test="stepper"')
        ->toContain('bg-primary')
        ->toContain('bg-hairline-strong')
        ->toContain('h-0.5 flex-1 bg-primary')
        ->toContain('text-xs')
        ->toContain('font-medium');
    expect(substr_count($html, 'data-tahap="selesai"'))->toBe(2)
        ->and(substr_count($html, 'data-tahap="belum"'))->toBe(1);
    expect(isiTitikStepper($html, 'selesai'))->toContain('<svg');
});

it('membiarkan titik tahap belum tanpa ikon dan beroutline', function () {
    $html = Blade::render('<x-ui.stepper :tahap="$tahap" :saat-ini="0" />', [
        'tahap' => ['Diajukan', 'Disetujui', 'Selesai'],
    ]);

    expect($html)->toContain('border-2 border-hairline-strong');
    expect(trim(isiTitikStepper($html, 'belum')))->toBe('');
});

it('menampilkan tahap ditolak merah tanpa centang dan label tetap tertulis', function () {
    $html = Blade::render('<x-ui.stepper :tahap="$tahap" :saat-ini="1" :ditolak="true" />', [
        'tahap' => ['Diajukan', 'Ditolak'],
    ]);

    expect($html)
        ->toContain('data-tahap="ditolak"')
        ->toContain('bg-danger')
        ->toContain('text-danger-deep')
        ->toContain('h-0.5 flex-1 bg-danger')
        ->toContain('Ditolak');

    $centang = isiTitikStepper($html, 'selesai');
    $silang = isiTitikStepper($html, 'ditolak');

    expect(trim($centang))->toContain('<svg')
        ->and(trim($silang))->toContain('<svg')
        ->and(trim($silang))->not->toBe(trim($centang));
});

it('menolak daftar tahap yang kosong', function () {
    try {
        Blade::render('<x-ui.stepper :tahap="[]" />');

        $this->fail('Seharusnya melempar exception tahap kosong');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Tahap stepper tidak boleh kosong');
    }
});
