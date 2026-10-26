<?php

use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;

/**
 * @return array{nasabah: User, kolektorPj: User, kolektorBukanPj: User, admin: User, produk: ProdukTabungan}
 */
function p26RekapFixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create([
        'name' => $opsi['nama'] ?? 'Nasabah Rekap P26',
    ]);
    $kolektorPj = User::factory()->kolektor()->create();
    $kolektorBukanPj = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    KolektorNasabah::create([
        'kolektor_id' => $kolektorPj->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas P26 Rekap',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $opsi['saldo'] ?? 60000,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 100000,
        'tanggal_transaksi' => now()->subDays(2)->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektorPj->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 40000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 40000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
        'waktu_approval' => now()->subDay(),
    ]);

    return [
        'nasabah' => $nasabah,
        'kolektorPj' => $kolektorPj,
        'kolektorBukanPj' => $kolektorBukanPj,
        'admin' => $admin,
        'produk' => $produk,
    ];
}

it('admin dan kolektor penanggung jawab dapat membuka rekap mutasi', function () {
    ['nasabah' => $nasabah, 'kolektorPj' => $kolektorPj, 'admin' => $admin] = p26RekapFixture();

    $this->actingAs($admin)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertSee('Rekap Mutasi')
        ->assertSee($nasabah->name)
        ->assertSee('Cetak');

    $this->actingAs($kolektorPj)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertSee('Rekap Mutasi');
});

it('menolak kolektor bukan penanggung jawab, nasabah, dan tamu', function () {
    ['nasabah' => $nasabah, 'kolektorBukanPj' => $kolektorBukanPj] = p26RekapFixture();

    $this->get(route('rekap.nasabah', $nasabah))
        ->assertRedirect();

    $this->actingAs($kolektorBukanPj)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertForbidden();

    $this->actingAs($nasabah)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertForbidden();
});

it('menampilkan kronologi setoran dan penarikan dengan saldo berjalan', function () {
    ['nasabah' => $nasabah, 'admin' => $admin] = p26RekapFixture();

    $this->actingAs($admin)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertSee('Setoran')
        ->assertSee('Penarikan')
        ->assertSee('Rp 100.000')
        ->assertSee('Rp 40.000')
        ->assertSee('Rp 60.000')
        ->assertSee('Saldo Berjalan');
});

it('mengabaikan setoran dibatalkan dan penarikan yang belum selesai', function () {
    ['nasabah' => $nasabah, 'admin' => $admin, 'produk' => $produk] = p26RekapFixture([
        'saldo' => 0,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 999999,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $admin->id,
        'sumber_input' => 'susulan',
        'status' => 'dibatalkan',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 555000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 555000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertDontSee('999.999')
        ->assertDontSee('555.000');
});

it('merender rekap nasabah offline tanpa error no_hp null', function () {
    ['nasabah' => $nasabah, 'admin' => $admin, 'produk' => $produk] = p26RekapFixture();

    $nasabah->update(['no_hp' => null, 'mode_akses' => 'offline']);

    $this->actingAs($admin)
        ->get(route('rekap.nasabah', $nasabah))
        ->assertOk()
        ->assertSee($nasabah->name)
        ->assertSee('Mode Offline');
});
