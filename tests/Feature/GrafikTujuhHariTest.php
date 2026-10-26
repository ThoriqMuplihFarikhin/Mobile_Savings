<?php

use App\Livewire\Nasabah\RiwayatTabungan;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

/**
 * Fase E butir E: grafik batang 7 hari di riwayat tabungan (DESIGN.md §14
 * baris Riwayat tabungan "Grafik batang 7 hari: butuh agregat per hari" dan §15
 * baris aksesibilitas "grafik batang punya padanan teks (aria-label)").
 * Jendela grafik tetap tujuh hari, mengikuti filter produk, status dibatalkan
 * dikecualikan seperti ringkasan produk.
 */
function grafik7HariFixture(): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => uniqid('Produk Grafik '),
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return [$nasabah, $produk];
}

function grafik7HariSetoran(User $nasabah, ProdukTabungan $produk, string $tanggal, float $nominal = 10000, string $status = 'tercatat'): void
{
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => $tanggal,
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => $status,
    ]);
}

it('menyusun tujuh titik agregat per hari dengan hari ini di ujung kanan', function () {
    [$nasabah, $produk] = grafik7HariFixture();

    grafik7HariSetoran($nasabah, $produk, Carbon::today()->toDateString(), 10000);
    grafik7HariSetoran($nasabah, $produk, Carbon::today()->subDay()->toDateString(), 5000);

    $titik = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class)->viewData('setoran7Hari');

    expect($titik)->toHaveCount(7);
    expect($titik[6]['tanggal'])->toBe(Carbon::today()->toDateString());
    expect((float) $titik[6]['total'])->toBe(10000.0);
    expect((float) $titik[5]['total'])->toBe(5000.0);
    expect((float) $titik[0]['total'])->toBe(0.0);
    expect($titik[6]['hari'])->not->toBe($titik[5]['hari']);
});

it('merender tujuh batang dengan padanan teks aria-label', function () {
    [$nasabah, $produk] = grafik7HariFixture();

    grafik7HariSetoran($nasabah, $produk, Carbon::today()->toDateString(), 50000);

    $html = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class)->html();

    expect(substr_count($html, 'rounded-t-[4px]'))->toBe(7);
    expect($html)->toContain('Setoran 7 hari terakhir');
    expect($html)->toContain('Grafik batang setoran tujuh hari terakhir');
    expect($html)->toContain('50.000 rupiah');
});

it('mengikuti filter produk dan mengecualikan setoran dibatalkan', function () {
    [$nasabah, $produkA] = grafik7HariFixture();
    $produkB = ProdukTabungan::create([
        'nama' => uniqid('Produk Grafik B '),
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $hariIni = Carbon::today()->toDateString();
    grafik7HariSetoran($nasabah, $produkA, $hariIni, 10000);
    grafik7HariSetoran($nasabah, $produkA, $hariIni, 77000, 'dibatalkan');
    grafik7HariSetoran($nasabah, $produkB, $hariIni, 20000);

    $component = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class);

    expect((float) $component->viewData('setoran7Hari')[6]['total'])->toBe(30000.0);

    $component->set('produkFilter', $produkA->id);

    expect((float) $component->viewData('setoran7Hari')[6]['total'])->toBe(10000.0);
});

it('mengabaikan setoran di luar jendela tujuh hari', function () {
    [$nasabah, $produk] = grafik7HariFixture();

    grafik7HariSetoran($nasabah, $produk, Carbon::today()->subDays(8)->toDateString(), 99000);

    $titik = Livewire::actingAs($nasabah)->test(RiwayatTabungan::class)->viewData('setoran7Hari');

    expect(collect($titik)->sum('total'))->toBe(0.0);
});
