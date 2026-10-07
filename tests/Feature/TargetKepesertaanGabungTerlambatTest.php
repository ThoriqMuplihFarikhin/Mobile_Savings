<?php

use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

/**
 * Kepesertaan gabung hari ke-10 dari periode 30 hari (mulai sudah lewat
 * 10 hari): totalHariKepesertaan = hari dari tanggal_mulai_ikut s/d
 * periode_selesai (bukan seluruh periode).
 *
 * @return array{nasabah: User, produk: ProdukTabungan, kepesertaan: KepesertaanPaket}
 */
function seedGabungTerlambat(): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket 30 Hari',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => now()->subDays(39)->toDateString(),
        'periode_selesai' => now()->subDays(10)->toDateString(),
        'tanggal_boleh_cair' => now()->subDays(9)->toDateString(),
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
    ]);

    return compact('nasabah', 'produk', 'kepesertaan');
}

it('menghitung totalHariKepesertaan dari tanggal mulai ikut s/d periode selesai', function () {
    $data = seedGabungTerlambat();

    // hari ke-10 s/d hari ke-30 periode = 21 hari, bukan 30.
    expect($data['kepesertaan']->totalHariKepesertaan())->toBe(21);
});

it('membatasi hitung hari berjalan dan tunggakan pada totalHariKepesertaan saat gabung hari ke-10', function () {
    $data = seedGabungTerlambat();

    $hasil = $data['kepesertaan']->hitungUlangKepesertaan();

    // Hari berjalan kasar sudah 31 (30 hari sejak gabung + 1), dibatasi 21.
    expect($hasil['tunggakan_hari'])->toBe(21)
        ->and($hasil['tunggakan_rupiah'])->toBe(210000.0)
        ->and((float) $data['kepesertaan']->fresh()->total_seharusnya_terkumpul)->toBe(210000.0);
});

it('menetapkan target kepesertaan = harga per hari kali totalHariKepesertaan', function () {
    $data = seedGabungTerlambat();

    expect($data['kepesertaan']->targetKepesertaan())->toBe(210000.0);
});

it('menampilkan target kepesertaan pada halaman progres nasabah', function () {
    $data = seedGabungTerlambat();

    $this->actingAs($data['nasabah']);
    Livewire::test(ProgresPaket::class)
        ->assertSee('Rp 210.000')
        ->assertDontSee('Rp 300.000');
});
