<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase E butir A: pilihan nominal cepat (chip-amount) pada layar ajukan
 * penarikan. DESIGN.md §7 "Input nominal dan pilihan cepat" dan §14 baris
 * "Ajukan penarikan — Pilihan nominal cepat: perilaku baru". Perilakunya
 * memakai state yang sudah ada ($nominal, $saldoList, $nominalMinimal)
 * tanpa query atau rute baru.
 */
function chipNominalCepatFixture(float $saldo): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => uniqid('Produk Chip '),
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => $saldo]);

    return [$nasabah, $produk];
}

it('menampilkan empat pilihan nominal cepat dengan gaya chip-amount', function () {
    [$nasabah] = chipNominalCepatFixture(100000);

    $html = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)->html();

    expect($html)
        ->toContain('data-test="chip-amount"', false)
        ->toContain('Rp 50 rb')
        ->toContain('Rp 100 rb')
        ->toContain('Rp 200 rb')
        ->toContain('Tarik habis')
        ->toContain('atau tarik habis')
        ->toContain('border-hairline-strong bg-surface', false);
});

it('chip yang cocok dengan isi kolom nominal memakai gaya aktif dan memicu rincian', function () {
    [$nasabah, $produk] = chipNominalCepatFixture(100000);

    $html = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', '100000')
        ->html();

    expect($html)
        ->toContain('border-primary bg-primary text-on-primary', false)
        ->toContain('Rincian transaksi', false);
});

it('tarik habis terisi dari saldo produk terpilih dan nonaktif sebelum produk dipilih', function () {
    [$nasabah, $produk] = chipNominalCepatFixture(100000);

    $component = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class);

    expect($component->html())
        ->toContain('wire:click="$set(\'nominal\', \'\')" disabled', false);

    $component->set('produkId', $produk->id);

    expect($component->html())
        ->toContain('wire:click="$set(\'nominal\', \'100000.00\')"', false)
        ->not->toContain('wire:click="$set(\'nominal\', \'100000.00\')" disabled', false);
});

it('menonaktifkan chip di bawah aturan minimal tetapi tetap mengizinkan tarik habis', function () {
    AdminSetting::set('penarikan_minimal', '100000');

    [$nasabah, $produk] = chipNominalCepatFixture(30000);

    $html = Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->html();

    expect($html)
        ->toContain('wire:click="$set(\'nominal\', \'50000\')" disabled', false)
        ->toContain('wire:click="$set(\'nominal\', \'30000.00\')"', false)
        ->not->toContain('wire:click="$set(\'nominal\', \'200000\')" disabled', false)
        ->not->toContain('wire:click="$set(\'nominal\', \'30000.00\')" disabled', false);
});
