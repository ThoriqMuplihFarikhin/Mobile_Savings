<?php

use App\Livewire\Admin\Komisi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

function createApprovedPenarikan(User $nasabah, User $admin, ProdukTabungan $produk, string $status = 'approved', float $nominal = 50000, float $komisi = 2500, ?string $waktuApproval = null): TransaksiPenarikan
{
    return TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => $nominal,
        'persen_komisi_terpakai' => $produk->persen_komisi,
        'nominal_komisi' => $komisi,
        'nominal_diterima' => $nominal - $komisi,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => $status,
        'disetujui_oleh' => $admin->id,
        'waktu_approval' => $waktuApproval ?? now(),
    ]);
}

it('only counts approved and selesai status for total komisi', function () {
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

    createApprovedPenarikan($nasabah, $admin, $produk, 'pending', 50000, 2500);
    createApprovedPenarikan($nasabah, $admin, $produk, 'approved', 100000, 5000);
    createApprovedPenarikan($nasabah, $admin, $produk, 'ditolak', 30000, 1500);
    createApprovedPenarikan($nasabah, $admin, $produk, 'selesai', 80000, 4000);

    $this->actingAs($admin);

    Livewire::test(Komisi::class)
        ->assertSet('totalKomisi', 9000)
        ->assertSet('totalTransaksi', 2);
});

it('filters by date range based on waktu_approval', function () {
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

    // Approved last month
    createApprovedPenarikan($nasabah, $admin, $produk, 'approved', 50000, 2500, now()->subMonth()->toDateString());
    // Approved this month
    createApprovedPenarikan($nasabah, $admin, $produk, 'approved', 100000, 5000, now()->toDateString());

    $this->actingAs($admin);

    // Default filter (this month) — should only count 1
    Livewire::test(Komisi::class)
        ->assertSet('totalKomisi', 5000)
        ->assertSet('totalTransaksi', 1);

    // Expand filter to include last month
    Livewire::test(Komisi::class)
        ->set('dariTanggal', now()->subMonth()->startOfMonth()->toDateString())
        ->assertSet('totalKomisi', 7500)
        ->assertSet('totalTransaksi', 2);
});

it('filters by produk', function () {
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

    $produk1 = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $produk2 = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 10.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    createApprovedPenarikan($nasabah, $admin, $produk1, 'approved', 50000, 2500);
    createApprovedPenarikan($nasabah, $admin, $produk2, 'approved', 100000, 10000);

    $this->actingAs($admin);

    Livewire::test(Komisi::class)
        ->set('produkId', $produk1->id)
        ->assertSet('totalKomisi', 2500)
        ->assertSet('totalTransaksi', 1);
});

it('resetFilter restores default values', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(Komisi::class)
        ->set('dariTanggal', '2025-01-01')
        ->set('sampaiTanggal', '2025-12-31')
        ->set('produkId', 999)
        ->call('resetFilter')
        ->assertSet('dariTanggal', now()->startOfMonth()->toDateString())
        ->assertSet('sampaiTanggal', now()->toDateString())
        ->assertSet('produkId', '');
});

it('allows admin to access komisi page via route', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/komisi')
        ->assertOk()
        ->assertSee('Komisi');
});

it('prevents non-admin from accessing komisi page', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah);

    $response = $this->get('/admin/komisi');

    $response->assertStatus(403);
});

it('dashboard shows correct total komisi for admin', function () {
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

    createApprovedPenarikan($nasabah, $admin, $produk, 'approved', 50000, 2500);
    createApprovedPenarikan($nasabah, $admin, $produk, 'selesai', 80000, 4000);
    createApprovedPenarikan($nasabah, $admin, $produk, 'pending', 30000, 1500);

    $this->actingAs($admin);

    $response = $this->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('Total Komisi');
    $response->assertSee('Rp 6.500');
});
