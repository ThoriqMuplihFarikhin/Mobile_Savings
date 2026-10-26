<?php

use App\Actions\Paket\HitungProgresBarangAction;
use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{nasabah: User, produk: ProdukTabungan, kepesertaan: KepesertaanPaket}
 */
function seedProgresBarangD15(float $totalAktual, array $isiPaket, bool $tampilkanHarga = false): array
{
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Sembako',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'harga_per_hari' => 10000,
        'isi_paket' => $isiPaket,
        'tampilkan_harga_ke_nasabah' => $tampilkanHarga,
        'status' => 'aktif',
    ]);
    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'total_aktual_terkumpul' => $totalAktual,
    ]);

    return compact('nasabah', 'produk', 'kepesertaan');
}

it('mengalokasikan terkumpul berurutan ke harga tiap item untuk admin', function () {
    ['kepesertaan' => $kepesertaan] = seedProgresBarangD15(25000, [
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 10000],
        ['nama' => 'Minyak', 'jumlah' => '2 L', 'harga' => 20000],
        ['nama' => 'Gula', 'jumlah' => '1 kg', 'harga' => 30000],
    ]);

    $hasil = (new HitungProgresBarangAction)->untukAdmin($kepesertaan);

    expect($hasil['totalHarga'])->toBe(60000.0)
        ->and($hasil['persenKeseluruhan'])->toBe(41.67)
        ->and($hasil['items'])->toHaveCount(3)
        ->and($hasil['items'][0]['status'])->toBe('tercapai')
        ->and($hasil['items'][0]['persen'])->toBe(100.0)
        ->and($hasil['items'][0]['sisa'])->toBe(0.0)
        ->and($hasil['items'][1]['status'])->toBe('berjalan')
        ->and($hasil['items'][1]['persen'])->toBe(75.0)
        ->and($hasil['items'][1]['sisa'])->toBe(5000.0)
        ->and($hasil['items'][2]['status'])->toBe('belum')
        ->and($hasil['items'][2]['persen'])->toBe(0.0)
        ->and($hasil['items'][2]['sisa'])->toBe(30000.0);
});

it('melewati item tanpa harga dan menjaga alokasi berurutan', function () {
    ['kepesertaan' => $kepesertaan] = seedProgresBarangD15(5000, [
        ['nama' => 'Karung', 'jumlah' => '10 pcs'],
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 20000],
    ]);

    $hasil = (new HitungProgresBarangAction)->untukAdmin($kepesertaan);

    expect($hasil['totalHarga'])->toBe(20000.0)
        ->and($hasil['persenKeseluruhan'])->toBe(25.0)
        ->and($hasil['items'][0]['harga'])->toBeNull()
        ->and($hasil['items'][0]['status'])->toBeNull()
        ->and($hasil['items'][0]['persen'])->toBeNull()
        ->and($hasil['items'][1]['dialokasikan'])->toBe(5000.0)
        ->and($hasil['items'][1]['status'])->toBe('berjalan');

    $tanpaHarga = (new HitungProgresBarangAction)->untukAdmin($kepesertaan);
    expect($tanpaHarga['items'][1]['harga'])->toBe(20000.0);
});

it('menyembunyikan harga dari nasabah kecuali produk mengizinkan', function () {
    ['kepesertaan' => $kepesertaan] = seedProgresBarangD15(10000, [
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 10000],
        ['nama' => 'Minyak', 'jumlah' => '2 L', 'harga' => 999999],
    ]);

    $aksi = new HitungProgresBarangAction;
    $tersembunyi = $aksi->untukNasabah($kepesertaan);

    expect($tersembunyi['items'][0])->not->toHaveKey('harga')
        ->and($tersembunyi['items'][0])->not->toHaveKey('sisa')
        ->and($tersembunyi)->not->toHaveKey('totalHarga')
        ->and($tersembunyi['items'][0]['status'])->toBe('tercapai')
        ->and($tersembunyi['persenKeseluruhan'])->toBe(0.99);

    $kepesertaan->produk->update(['tampilkan_harga_ke_nasabah' => true]);
    $terlihat = $aksi->untukNasabah($kepesertaan->fresh('produk'));

    expect($terlihat['items'][1]['harga'])->toBe(999999.0)
        ->and($terlihat['totalHarga'])->toBe(1009999.0);
});

it('halaman progres nasabah tidak membocorkan harga item', function () {
    ['nasabah' => $nasabah, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedProgresBarangD15(10000, [
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 999999],
        ['nama' => 'Minyak', 'jumlah' => '2 L'],
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaan->id,
        'nominal' => 10000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now()->toDateTimeString(),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $this->actingAs($nasabah);
    $component = Livewire::test(ProgresPaket::class)
        ->assertDontSee('999999');

    $progres = collect($component->viewData('progres'));
    $milik = $progres->firstWhere('kepesertaan_id', $kepesertaan->id);

    expect($milik)->not->toBeNull();
    expect(json_encode($milik))->not->toContain('"harga"')
        ->and($milik['items'][0]['status'])->toBe('berjalan')
        ->and($milik['persenKeseluruhan'])->toBe(1.0);
});

it('halaman landing tidak membocorkan harga item paket', function () {
    seedProgresBarangD15(0, [
        ['nama' => 'Beras', 'jumlah' => '5 kg', 'harga' => 999999],
    ]);

    $this->get('/')->assertOk()->assertDontSee('999999');
});
