<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Nasabah\AjukanPenarikan;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Services\WhatsAppService;
use Livewire\Livewire;

it('allows nasabah to submit withdrawal request', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

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
        'saldo' => 100000,
    ]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'status' => 'pending',
        'jalur_pengajuan' => 'online',
    ]);
});

it('rejects withdrawal when saldo is insufficient', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

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
        'saldo' => 10000,
    ]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 50000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
    ]);
});

it('allows admin to approve withdrawal and decrement saldo', function () {
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
        'saldo' => 100000,
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'approved',
    ]);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('50000.00');
});

it('allows admin to reject withdrawal', function () {
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
        'saldo' => 100000,
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('reject', $penarikan->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'ditolak',
    ]);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('100000.00');
});

it('blocks withdrawal for paket before tanggal_boleh_cair', function () {
    $nasabah = User::factory()->nasabah()->create();
    $admin = User::factory()->admin()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Lebaran',
        'tipe' => 'paket',
        'persen_komisi' => 10.00,
        'harga_per_hari' => 20000,
        'tanggal_boleh_cair' => now()->addMonths(6)->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 200000,
    ]);

    $this->actingAs($nasabah);

    Livewire::test(AjukanPenarikan::class)
        ->set('produkId', $produk->id)
        ->set('nominal', 100000)
        ->set('lokasi_pengambilan', 'kantor')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseMissing('transaksi_penarikan', [
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 100000,
    ]);
});

it('approves withdrawal even when WhatsApp notification fails', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['no_hp' => '6281234567890']);

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
        'saldo' => 100000,
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $whatsappFake = new class extends WhatsAppService
    {
        public function sendNotification(string $phone, string $message): bool
        {
            throw new RuntimeException('WhatsApp gateway is down');
        }
    };

    $this->app->instance(WhatsAppService::class, $whatsappFake);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'approved',
    ]);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('50000.00');
});

it('rejects withdrawal even when WhatsApp notification fails', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['no_hp' => '6281234567890']);

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
        'saldo' => 100000,
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $whatsappFake = new class extends WhatsAppService
    {
        public function sendNotification(string $phone, string $message): bool
        {
            throw new RuntimeException('WhatsApp gateway is down');
        }
    };

    $this->app->instance(WhatsAppService::class, $whatsappFake);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('reject', $penarikan->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'ditolak',
    ]);

    $saldo = SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->first();
    expect($saldo->saldo)->toBe('100000.00');
});
