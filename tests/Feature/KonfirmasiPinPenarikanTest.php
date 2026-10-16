<?php

use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fase E butir B: sheet konfirmasi + langkah PIN pada pengajuan penarikan
 * online. DESIGN.md §8 baris "Konfirmasi penarikan menampilkan nominal,
 * komisi, jumlah diterima, lokasi pengambilan sebelum PIN diminta" dan §7
 * "Sheet". Verifikasi PIN mengikuti pola domain IkutiPaketAction (Hash,
 * rate-limit 5 kali / 15 menit, pesan "PIN salah. Sisa percobaan: N.").
 */
function konfirmasiPinPenarikanFixture(float $saldo = 100000): array
{
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => uniqid('Produk Konfirmasi '),
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => $saldo]);

    return [$nasabah, $produk];
}

function konfirmasiPinPenarikanBukaSheet(User $nasabah, ProdukTabungan $produk): mixed
{
    return Livewire::actingAs($nasabah)->test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit');
}

it('submit membuka sheet konfirmasi berisi rincian tanpa menyimpan pengajuan', function () {
    [$nasabah, $produk] = konfirmasiPinPenarikanFixture();

    konfirmasiPinPenarikanBukaSheet($nasabah, $produk)
        ->assertHasNoErrors()
        ->assertSet('tampilKonfirmasi', true)
        ->assertSee('Konfirmasi penarikan')
        ->assertSee('Nominal diminta')
        ->assertSee('Total diterima')
        ->assertSee('Lokasi pengambilan');

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
    ]);
});

it('konfirmasi dengan PIN benar menyimpan pengajuan dan menutup sheet', function () {
    [$nasabah, $produk] = konfirmasiPinPenarikanFixture();

    konfirmasiPinPenarikanBukaSheet($nasabah, $produk)
        ->set('pin', '123456')
        ->call('konfirmasi')
        ->assertHasNoErrors()
        ->assertSet('tampilKonfirmasi', false)
        ->assertSet('pesanError', null);

    $this->assertDatabaseHas('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'status' => 'pending',
        'jalur_pengajuan' => 'online',
    ]);
});

it('konfirmasi menolak PIN salah tanpa menyimpan dan sheet tetap terbuka', function () {
    [$nasabah, $produk] = konfirmasiPinPenarikanFixture();

    konfirmasiPinPenarikanBukaSheet($nasabah, $produk)
        ->set('pin', '000000')
        ->call('konfirmasi')
        ->assertSet('tampilKonfirmasi', true)
        ->assertSet('pesanError', 'PIN salah. Sisa percobaan: 4.');

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
    ]);
});

it('konfirmasi mewajibkan PIN diisi', function () {
    [$nasabah, $produk] = konfirmasiPinPenarikanFixture();

    konfirmasiPinPenarikanBukaSheet($nasabah, $produk)
        ->call('konfirmasi')
        ->assertHasErrors(['pin' => 'required']);
});

it('membatasi percobaan PIN salah lalu mengunci sementara', function () {
    [$nasabah, $produk] = konfirmasiPinPenarikanFixture();

    $component = konfirmasiPinPenarikanBukaSheet($nasabah, $produk);

    foreach ([4, 3, 2, 1, 0] as $sisa) {
        $component->set('pin', '000000')
            ->call('konfirmasi')
            ->assertSet('pesanError', "PIN salah. Sisa percobaan: {$sisa}.");
    }

    $component->set('pin', '123456')
        ->call('konfirmasi')
        ->assertSet('pesanError', 'Terlalu banyak percobaan PIN. Coba lagi nanti atau hubungi admin.');

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
    ]);
});
