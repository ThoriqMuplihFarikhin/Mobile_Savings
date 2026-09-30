<?php

use App\Livewire\Admin\Laporan;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedLaporan(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function setoranLaporan(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal, string $tanggal, string $status = 'tercatat'): TransaksiSetoran
{
    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => $status,
    ]);
}

it('hanya menghitung setoran sesuai tanggal transaksi, bukan waktu input', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporan();

    setoranLaporan($kolektor, $nasabah, $produk, 50000, now()->subDay()->toDateString());
    setoranLaporan($kolektor, $nasabah, $produk, 30000, now()->toDateString());

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class);

    expect((float) $component->viewData('totalSetoran'))->toBe(30000.00)
        ->and((int) $component->viewData('jumlahTransaksiSetoran'))->toBe(1);
});

it('tidak menghitung setoran yang dibatalkan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporan();

    setoranLaporan($kolektor, $nasabah, $produk, 50000, now()->toDateString(), 'dibatalkan');

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class);

    expect((float) $component->viewData('totalSetoran'))->toBe(0.00)
        ->and((int) $component->viewData('jumlahTransaksiSetoran'))->toBe(0);
});

it('mengamankan sel teks formula saat ekspor csv', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporan();

    $nasabah->update(['name' => '=HYPERLINK("http://evil.example","klik")']);
    setoranLaporan($kolektor, $nasabah, $produk, 50000, now()->toDateString());

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class);
    $component->call('exportCsv');

    $content = base64_decode((string) data_get($component->effects, 'download.content'));

    expect($content)->toContain("'=HYPERLINK")
        ->and($content)->not->toContain("\n=HYPERLINK");
    $component->assertFileDownloaded('laporan-harian-'.now()->toDateString().'.csv');
});

it('menamai file laporan bulanan dengan bulan, bukan tanggal', function () {
    ['admin' => $admin] = seedLaporan();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->set('periode', 'bulanan')
        ->call('exportCsv')
        ->assertFileDownloaded('laporan-bulanan-'.now()->format('Y-m').'.csv');
});

it('menolak periode yang tidak dikenal saat render', function () {
    ['admin' => $admin] = seedLaporan();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->set('periode', 'tidak-ada')
        ->assertHasErrors(['periode']);
});

it('menolak bulan yang tidak valid saat mode bulanan', function () {
    ['admin' => $admin] = seedLaporan();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->set('periode', 'bulanan')
        ->set('bulan', 'bukan-bulan')
        ->assertHasErrors(['bulan']);
});

it('menolak tanggal yang tidak valid dan mengosongkan laporan', function () {
    ['admin' => $admin] = seedLaporan();

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('tanggal', 'bukan-tanggal')
        ->assertHasErrors(['tanggal']);

    expect((float) $component->viewData('totalSetoran'))->toBe(0.00);

    $component->call('exportCsv')
        ->assertHasErrors(['tanggal'])
        ->assertNoFileDownloaded();
});
