<?php

use App\Livewire\Admin\ManajemenProduk;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * @return array<string, mixed>
 */
function formPaketHargaItem(array $tambahan = []): array
{
    return array_merge([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'harga_per_hari' => '10000',
        'periode_mulai' => now()->toDateString(),
        'periode_selesai' => now()->addDays(9)->toDateString(),
        'tanggal_boleh_cair' => now()->addDays(10)->toDateString(),
        'batas_toleransi' => '3',
    ], $tambahan);
}

function isiFormPaketHargaItem($component, array $fields): mixed
{
    foreach ($fields as $kunci => $nilai) {
        $component->set($kunci, $nilai);
    }

    return $component;
}

it('menyimpan harga per item dan uang tunai', function () {
    $component = componentManajemenProdukHarga();
    isiFormPaketHargaItem($component, formPaketHargaItem([
        'isiPaketItems' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => '10000'],
            ['nama' => 'Karung', 'jumlah' => '10 pcs', 'harga' => ''],
        ],
        'uang_tunai' => '50000',
    ]))->call('save');

    $produk = ProdukTabungan::latest('id')->firstOrFail();
    $items = $produk->isi_paket;

    expect($items)->toHaveCount(3)
        ->and((float) $items[0]['harga'])->toBe(10000.0)
        ->and($items[1])->not->toHaveKey('harga')
        ->and($items[2]['nama'])->toBe('Uang Tunai')
        ->and((float) $items[2]['harga'])->toBe(50000.0);
});

it('menolak harga item negatif', function () {
    $component = componentManajemenProdukHarga();
    isiFormPaketHargaItem($component, formPaketHargaItem([
        'isiPaketItems' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => '-500'],
        ],
    ]))->call('save')->assertHasErrors('isiPaketItems.0.harga');
});

it('menukar urutan item paket', function () {
    $component = componentManajemenProdukHarga();
    isiFormPaketHargaItem($component, formPaketHargaItem([
        'isiPaketItems' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => ''],
            ['nama' => 'Minyak', 'jumlah' => '2 L', 'harga' => ''],
        ],
    ]));

    $component->call('turunkanItemPaket', 0);
    expect(collect($component->viewData('isiPaketItems'))->pluck('nama')->all())
        ->toBe(['Minyak', 'Beras']);

    $component->call('naikkanItemPaket', 1);
    expect(collect($component->viewData('isiPaketItems'))->pluck('nama')->all())
        ->toBe(['Beras', 'Minyak']);

    $component->call('naikkanItemPaket', 0);
    $component->call('turunkanItemPaket', 1);
    expect(collect($component->viewData('isiPaketItems'))->pluck('nama')->all())
        ->toBe(['Beras', 'Minyak']);
});

it('menghitung ringkas total harga terhadap target', function () {
    $component = componentManajemenProdukHarga();
    isiFormPaketHargaItem($component, formPaketHargaItem([
        'isiPaketItems' => [
            ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => '20000'],
            ['nama' => 'Minyak', 'jumlah' => '2 L', 'harga' => '10000'],
        ],
    ]));

    $ringkas = $component->viewData('ringkasHarga');
    expect($ringkas['totalHarga'])->toBe(30000.0)
        ->and($ringkas['target'])->toBe(100000.0)
        ->and($ringkas['selisih'])->toBe(70000.0)
        ->and($ringkas['melebihi'])->toBeFalse();

    $component->set('isiPaketItems', [
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => '150000'],
    ]);
    $ringkas = $component->viewData('ringkasHarga');
    expect($ringkas['totalHarga'])->toBe(150000.0)
        ->and($ringkas['melebihi'])->toBeTrue()
        ->and($ringkas['selisih'])->toBe(-50000.0);
});

it('menyimpan opsi cair saat target dan batas daftar', function () {
    $component = componentManajemenProdukHarga();
    isiFormPaketHargaItem($component, formPaketHargaItem([
        'isiPaketItems' => [['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => '10000']],
        'tampilkan_harga_ke_nasabah' => true,
        'boleh_cair_saat_target' => true,
        'batas_daftar_hingga' => now()->addDay()->toDateString(),
    ]))->call('save');

    $produk = ProdukTabungan::latest('id')->firstOrFail();
    expect($produk->tampilkan_harga_ke_nasabah)->toBeTrue()
        ->and($produk->boleh_cair_saat_target)->toBeTrue()
        ->and($produk->batas_daftar_hingga->toDateString())->toBe(now()->addDay()->toDateString());

    $component->call('edit', $produk->id);
    expect($component->get('tampilkan_harga_ke_nasabah'))->toBeTrue()
        ->and($component->get('boleh_cair_saat_target'))->toBeTrue()
        ->and($component->get('batas_daftar_hingga'))->toBe(now()->addDay()->toDateString());
});

it('tetap memuat item lama tanpa kunci harga saat diedit', function () {
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lama',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'isi_paket' => [['nama' => 'Gula', 'jumlah' => '1 kg']],
        'status' => 'aktif',
    ]);

    componentManajemenProdukHarga()
        ->call('edit', $produk->id)
        ->assertSet('isiPaketItems.0.nama', 'Gula')
        ->assertHasNoErrors();
});

function componentManajemenProdukHarga(): Testable
{
    $admin = User::factory()->admin()->create();

    return Livewire::actingAs($admin)->test(ManajemenProduk::class);
}
