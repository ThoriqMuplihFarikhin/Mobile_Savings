<?php

use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedKasDikoreksi(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Kas',
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function buatSetoranKas(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal, string $status = 'tercatat'): TransaksiSetoran
{
    SaldoProduk::firstOrCreate(
        ['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id],
        ['saldo' => 0]
    );

    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => $status,
    ]);
}

it('menghitung setoran dikoreksi pada total setor kantor dan mengabaikan yang dibatalkan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKasDikoreksi();

    $dikoreksi = buatSetoranKas($kolektor, $nasabah, $produk, 50000);
    $dibatalkan = buatSetoranKas($kolektor, $nasabah, $produk, 30000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $dikoreksi->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Salah ketik nominal')
        ->call('koreksi')
        ->assertHasNoErrors();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $dibatalkan->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal')
        ->assertHasNoErrors();

    $this->actingAs($kolektor);
    $component = Livewire::test(SetorKantor::class);

    expect(round((float) $component->get('totalBelumDisetor'), 2))->toBe(60000.00)
        ->and((int) $component->get('jumlahTransaksi'))->toBe(1);
});

it('menjadikan hasUnsettledCash true untuk setoran dikoreksi dan false untuk yang dibatalkan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKasDikoreksi();

    $dibatalkan = buatSetoranKas($kolektor, $nasabah, $produk, 30000);
    expect($kolektor->refresh()->hasUnsettledCash())->toBeTrue();

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $dibatalkan->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal');

    expect($kolektor->refresh()->hasUnsettledCash())->toBeFalse();

    $dikoreksi = buatSetoranKas($kolektor, $nasabah, $produk, 50000);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $dikoreksi->id)
        ->set('nominalBaru', 45000)
        ->set('alasanKoreksi', 'Salah ketik')
        ->call('koreksi');

    expect($kolektor->refresh()->hasUnsettledCash())->toBeTrue();
});

it('menampilkan setoran dikoreksi di riwayat dashboard nasabah dan menyembunyikan yang dibatalkan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKasDikoreksi();

    $dikoreksi = buatSetoranKas($kolektor, $nasabah, $produk, 50000);
    $dibatalkan = buatSetoranKas($kolektor, $nasabah, $produk, 30000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $dikoreksi->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Salah ketik nominal')
        ->call('koreksi');

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $dibatalkan->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal');

    $this->actingAs($nasabah);
    $response = $this->get(route('dashboard'));
    $response->assertOk();

    $riwayat = collect($response->viewData('riwayatGabungan'));
    expect($riwayat->where('type', 'setoran')->pluck('nominal')->map(fn ($n) => (float) $n)->all())
        ->toBe([60000.00]);
});

it('menghitung setoran dikoreksi pada rekap rekonsiliasi admin', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedKasDikoreksi();

    $dikoreksi = buatSetoranKas($kolektor, $nasabah, $produk, 50000);
    $dibatalkan = buatSetoranKas($kolektor, $nasabah, $produk, 30000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $dikoreksi->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Salah ketik nominal')
        ->call('koreksi');

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $dibatalkan->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal');

    $component = Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id);

    expect(round((float) $component->get('totalSeharusnya'), 2))->toBe(60000.00);
});
