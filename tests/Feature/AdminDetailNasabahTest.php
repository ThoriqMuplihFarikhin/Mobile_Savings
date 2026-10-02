<?php

use App\Livewire\Admin\DetailNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function createDetailNasabahFixture(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 120000,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->subDays(10)->toDateString(),
        'tanggal_input_sistem' => now()->subDays(10),
        'input_by' => $admin->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1500,
        'nominal_diterima' => 28500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
        'waktu_approval' => now()->subDays(5),
    ]);

    return [$admin, $nasabah, $penarikan];
}

it('includes approved withdrawals in history with approval date', function () {
    [$admin, $nasabah] = createDetailNasabahFixture();

    $this->actingAs($admin);

    $component = Livewire::test(DetailNasabah::class, ['user' => $nasabah]);

    $riwayat = $component->viewData('riwayat');
    $penarikanRiwayat = $riwayat->firstWhere('tipe', 'Penarikan');

    expect($penarikanRiwayat)->not->toBeNull();
    expect($penarikanRiwayat['status'])->toBe('approved');
    expect($penarikanRiwayat['tanggal'])->toBe(now()->subDays(5)->format('Y-m-d'));
});

it('closes the area path at the graph baseline', function () {
    [$admin, $nasabah] = createDetailNasabahFixture();

    $this->actingAs($admin);

    $chart = Livewire::test(DetailNasabah::class, ['user' => $nasabah])->viewData('chartData');

    expect($chart['areaD'])->toEndWith(','.($chart['padding'] + $chart['graphHeight']).' Z');
});

it('keeps ending balance curve equal to current balance', function () {
    [$admin, $nasabah] = createDetailNasabahFixture();

    $this->actingAs($admin);

    $chart = Livewire::test(DetailNasabah::class, ['user' => $nasabah])->viewData('chartData');
    $saldoSekarang = (float) SaldoProduk::where('nasabah_id', $nasabah->id)->sum('saldo');

    expect(abs($chart['saldoAkhir'] - $saldoSekarang))->toBeLessThan(0.01);
});

it('allows admin to access detail nasabah via Livewire', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($admin);

    Livewire::test(DetailNasabah::class, ['user' => $nasabah])
        ->assertStatus(200)
        ->assertSet('user.id', $nasabah->id);
});

it('allows admin to access detail nasabah via route', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.nasabah.detail', $nasabah->id))
        ->assertOk()
        ->assertSee('Detail Nasabah');
});

it('blocks kolektor from accessing detail nasabah via route', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($kolektor)
        ->get(route('admin.nasabah.detail', $nasabah->id))
        ->assertForbidden();
});

it('blocks nasabah from accessing detail nasabah via route', function () {
    $nasabah1 = User::factory()->nasabah()->create();
    $nasabah2 = User::factory()->nasabah()->create();

    $this->actingAs($nasabah1)
        ->get(route('admin.nasabah.detail', $nasabah2->id))
        ->assertForbidden();
});

it('returns 404 when accessing non-nasabah user via route', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($admin)
        ->get(route('admin.nasabah.detail', $kolektor->id))
        ->assertNotFound();
});
