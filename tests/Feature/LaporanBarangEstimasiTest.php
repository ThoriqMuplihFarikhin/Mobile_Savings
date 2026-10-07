<?php

use App\Livewire\Admin\Laporan;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, nasabah: User, produk: ProdukTabungan}
 */
function seedBarangEstimasiD15(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Estimasi',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 10000,
        'isi_paket' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 10000],
            ['nama' => 'Karung', 'jumlah' => '10 pcs'],
        ],
        'status' => 'aktif',
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(3)->toDateString(),
    ]);

    return compact('admin', 'nasabah', 'produk');
}

it('menambah estimasi biaya per item pada laporan kebutuhan barang', function () {
    ['admin' => $admin] = seedBarangEstimasiD15();

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'barang')
        ->assertSee('Estimasi Biaya');

    $rows = collect($component->viewData('barangRows'));
    $beras = $rows->firstWhere('item', 'Beras');
    $karung = $rows->firstWhere('item', 'Karung');

    expect($beras['estimasi'])->toBe(10000.0)
        ->and($beras['total'])->toBe('5 kg')
        ->and($karung['estimasi'])->toBeNull();
});

it('mengunduh csv kebutuhan barang beserta estimasi biaya', function () {
    ['admin' => $admin] = seedBarangEstimasiD15();

    $this->actingAs($admin);
    $tes = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'barang')
        ->call('exportCsv');

    $csv = base64_decode((string) data_get($tes->effects, 'download.content'));

    expect(str_contains($csv, 'Estimasi Biaya'))->toBeTrue()
        ->and(str_contains($csv, '10000'))->toBeTrue();
});
