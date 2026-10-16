<?php

/**
 * Kontras bertahap #3: `--color-hairline-strong` adalah border input dan
 * kontrol interaktif (DESIGN.md §warna "Hairline strong: border input"), jadi
 * kena syarat WCAG 1.4.11 (≥3:1 untuk batas komponen antarmuka). Nilai lama
 * #d4d4d4 (≈1,5:1 di putih) dan #404040 (≈1,6:1 di kartu #171717) gagal;
 * kini #858585 (terang) dan #808080 (gelap). Nilai token tidak muncul di
 * HTML render, jadi tes membaca resources/css/app.css dan menghitung rasio
 * kontrasnya terhadap latar tempat border itu dipakai.
 *
 * Audit ulang K3: usulan #8f8f8f ternyata turun ke 2,94:1 di canvas-warm
 * (#f3f7f1), jadi nilai token dipertahankan #858585/#808080 dan latar
 * canvas-warm kini ikut diperiksa pada mode terang.
 */
function potonganAppCss(string $penanda, string $penutup): string
{
    $css = file_get_contents(dirname(__DIR__, 2).'/resources/css/app.css');
    $mulai = strpos($css, $penanda);
    $panjang = strpos($css, $penutup, $mulai) - $mulai;

    return preg_replace('!/\*.*?\*/!s', '', substr($css, $mulai, $panjang));
}

function nilaiTokenWarna(string $blok, string $token): string
{
    preg_match('/'.preg_quote($token, '/').':\s*(#[0-9a-fA-F]{6})/', $blok, $cocok);

    return $cocok[1] ?? '';
}

function rasioKontrasWcag(string $hexA, string $hexB): float
{
    $luminansi = static function (string $hex): float {
        $hex = ltrim($hex, '#');
        $linier = array_map(static fn (string $dua): float => (int) hexdec($dua) / 255 <= 0.04045
            ? ((int) hexdec($dua) / 255) / 12.92
            : pow(((int) hexdec($dua) / 255 + 0.055) / 1.055, 2.4), [
                substr($hex, 0, 2),
                substr($hex, 2, 2),
                substr($hex, 4, 2),
            ]);

        return 0.2126 * $linier[0] + 0.7152 * $linier[1] + 0.0722 * $linier[2];
    };

    $a = $luminansi($hexA);
    $b = $luminansi($hexB);

    return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
}

it('hairline-strong mode terang memegang minimal 3:1 di latar input', function () {
    $hex = nilaiTokenWarna(potonganAppCss('@theme {', "\n}"), '--color-hairline-strong');

    expect($hex)->not->toBe('');
    expect(rasioKontrasWcag($hex, '#ffffff'))->toBeGreaterThanOrEqual(3.0);
    expect(rasioKontrasWcag($hex, '#f5f5f5'))->toBeGreaterThanOrEqual(3.0);
});

it('hairline-strong mode gelap memegang minimal 3:1 di semua latar gelap', function () {
    $hex = nilaiTokenWarna(potonganAppCss('.dark {', "\n    }"), '--color-hairline-strong');

    expect($hex)->not->toBe('');
    expect(rasioKontrasWcag($hex, '#171717'))->toBeGreaterThanOrEqual(3.0);
    expect(rasioKontrasWcag($hex, '#18181b'))->toBeGreaterThanOrEqual(3.0);
    expect(rasioKontrasWcag($hex, '#262626'))->toBeGreaterThanOrEqual(3.0);
});
