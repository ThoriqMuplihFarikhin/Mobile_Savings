<?php

use App\Livewire\Kolektor\TransaksiHariIni;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedTransaksiHariIni(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Harian',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return compact('kolektor', 'nasabah', 'produk');
}

it('halaman transaksi hari ini hanya dapat diakses oleh kolektor', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah] = seedTransaksiHariIni();

    $this->get(route('kolektor.transaksi.index'))->assertRedirect(route('login'));

    $this->actingAs($nasabah)->get(route('kolektor.transaksi.index'))->assertForbidden();

    $this->actingAs($kolektor)->get(route('kolektor.transaksi.index'))->assertOk();
});

it('menampilkan setoran dan penarikan hari ini dengan kalkulasi ringkasan yang benar', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedTransaksiHariIni();

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 75000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 25000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 25000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'selesai',
        'waktu_pencairan' => now(),
        'dibayar_oleh' => $kolektor->id,
    ]);

    $component = Livewire::actingAs($kolektor)->test(TransaksiHariIni::class);

    $component->assertOk()
        ->assertViewHas('totalSetoranMasuk', 75000.0)
        ->assertViewHas('totalPenarikanTunai', 25000.0)
        ->assertSee('Transaksi hari ini')
        ->assertSee('Setoran masuk')
        ->assertSee('Penarikan tunai')
        ->assertSee('75.000', false)
        ->assertSee('25.000', false)
        ->assertSee($nasabah->name)
        ->assertSee('Tabungan Harian');
});

it('dapat memfilter transaksi berdasarkan jenis', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedTransaksiHariIni();

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 20000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 20000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'selesai',
        'waktu_pencairan' => now(),
        'dibayar_oleh' => $kolektor->id,
    ]);

    $component = Livewire::actingAs($kolektor)
        ->test(TransaksiHariIni::class)
        ->call('setFilter', 'setoran');

    expect($component->get('filter'))->toBe('setoran');

    $component->call('setFilter', 'penarikan');
    expect($component->get('filter'))->toBe('penarikan');
});

it('layar transaksi hari ini memakai token desain tanpa gradien', function () {
    ['kolektor' => $kolektor] = seedTransaksiHariIni();

    $html = Livewire::actingAs($kolektor)->test(TransaksiHariIni::class)->html();

    expect(array_filter([
        'bg-gradient',
        'blur-',
        'font-mono',
        'text-[9px]',
        'text-[10px]',
        'text-[11px]',
        'font-bold',
        'font-extrabold',
        'font-black',
        'rounded-3xl',
        'uppercase tracking',
        'shadow-2xl',
    ], fn ($pola) => str_contains($html, $pola)))->toBe([]);
});
