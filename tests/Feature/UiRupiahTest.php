<?php

use App\Models\User;
use Illuminate\Support\Facades\Blade;

it('memformat nilai dengan Rp, spasi tak-terputus, dan angka tabular', function () {
    $html = Blade::render('<x-ui.rupiah :nilai="1250000" />');

    expect($html)
        ->toContain('data-test="rupiah"')
        ->toContain('Rp&nbsp;1.250.000')
        ->toContain('tabular-nums')
        ->not->toContain('font-mono');
});

it('menandai uang masuk dengan plus hijau dan uang keluar dengan minus ink', function () {
    $masuk = Blade::render('<x-ui.rupiah :nilai="20000" tanda="+" />');
    $keluar = Blade::render('<x-ui.rupiah :nilai="100000" tanda="-" />');

    expect($masuk)
        ->toContain('+Rp&nbsp;20.000')
        ->toContain('text-primary')
        ->and($keluar)
        ->toContain("\u{2212}Rp&nbsp;100.000")
        ->toContain('text-ink')
        ->not->toContain('text-primary')
        ->not->toContain('text-danger');
});

it('memakai spasi biasa di mode cetak', function () {
    $html = Blade::render('<x-ui.rupiah :nilai="100000" :cetak="true" />');

    expect($html)
        ->toContain('Rp 100.000')
        ->not->toContain('&nbsp;');
});

it('menolak tanda yang tidak dikenal', function () {
    try {
        Blade::render('<x-ui.rupiah :nilai="1000" tanda="?" />');

        $this->fail('Seharusnya melempar exception tanda tidak dikenal');
    } catch (Throwable $e) {
        expect($e->getMessage())->toContain('Tanda rupiah tidak dikenal: ?');
    }
});

it('halaman saldo memakai komponen rupiah untuk nominalnya', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get(route('nasabah.saldo.index'))
        ->assertOk()
        ->assertSee('data-test="rupiah"', false)
        ->assertSee('Rp&nbsp;', false);
});
