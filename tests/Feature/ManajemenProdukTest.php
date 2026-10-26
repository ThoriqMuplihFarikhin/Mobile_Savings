<?php

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Admin\ManajemenProduk;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;

it('rejects produk paket without periode and tanggal cair', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->set('nama', 'Paket Tanpa Tanggal')
        ->set('tipe', 'paket')
        ->set('harga_per_hari', 5500)
        ->call('save')
        ->assertHasErrors(['periode_mulai', 'periode_selesai', 'tanggal_boleh_cair']);

    $this->assertDatabaseMissing('produk_tabungan', ['nama' => 'Paket Tanpa Tanggal']);
});

it('rejects paket with zero harga per hari and out-of-order dates', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->set('nama', 'Paket Tanggal Rusak')
        ->set('tipe', 'paket')
        ->set('harga_per_hari', 0)
        ->set('periode_mulai', now()->addDays(10)->toDateString())
        ->set('periode_selesai', now()->addDays(1)->toDateString())
        ->set('tanggal_boleh_cair', now()->subDays(5)->toDateString())
        ->call('save')
        ->assertHasErrors(['harga_per_hari', 'periode_selesai', 'tanggal_boleh_cair']);

    $this->assertDatabaseMissing('produk_tabungan', ['nama' => 'Paket Tanggal Rusak']);
});

it('keeps produk nonaktif when edited', function () {
    $admin = User::factory()->admin()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Produk Nonaktif',
        'tipe' => 'bebas',
        'persen_komisi' => 5,
        'status' => 'nonaktif',
    ]);

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->call('edit', $produk->id)
        ->set('nama', 'Produk Nonaktif Diedit')
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('produk_tabungan', [
        'id' => $produk->id,
        'nama' => 'Produk Nonaktif Diedit',
        'status' => 'nonaktif',
    ]);
});

it('does not crash on missing product id for edit, save, and delete', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->call('edit', 999999)
        ->assertSet('editId', null)
        ->assertSet('showForm', false)
        ->set('nama', 'Tanpa Id')
        ->set('persen_komisi', 5)
        ->set('editId', 999999)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('produk_tabungan', ['nama' => 'Tanpa Id']);

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->call('confirmDelete', 999999)
        ->call('delete')
        ->assertSet('tampilKonfirmasiHapus', false);
});

it('blocks penarikan for paket without tanggal_boleh_cair', function () {
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Belum Cair',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    expect(fn () => (new AjukanPenarikanAction)->execute($nasabah, $produk, 50000, 'online'))
        ->toThrow(InvalidArgumentException::class, 'Tanggal pencairan paket belum ditetapkan');
});

it('creates valid paket with aktif status and clears paket dates for bebas', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->set('nama', 'Paket Sah')
        ->set('tipe', 'paket')
        ->set('harga_per_hari', 5500)
        ->set('periode_mulai', now()->subDays(30)->toDateString())
        ->set('periode_selesai', now()->subDays(10)->toDateString())
        ->set('tanggal_boleh_cair', now()->subDays(10)->toDateString())
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('produk_tabungan', [
        'nama' => 'Paket Sah',
        'tipe' => 'paket',
        'status' => 'aktif',
    ]);

    Livewire::actingAs($admin)->test(ManajemenProduk::class)
        ->set('tipe', 'paket')
        ->set('nama', 'Bebas Dari Paket')
        ->set('harga_per_hari', 5500)
        ->set('periode_mulai', now()->subDays(30)->toDateString())
        ->set('periode_selesai', now()->subDays(10)->toDateString())
        ->set('tanggal_boleh_cair', now()->subDays(10)->toDateString())
        ->set('tipe', 'bebas')
        ->set('persen_komisi', 5)
        ->call('save')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('produk_tabungan', [
        'nama' => 'Bebas Dari Paket',
        'tipe' => 'bebas',
        'tanggal_boleh_cair' => null,
        'periode_mulai' => null,
        'periode_selesai' => null,
    ]);
});
