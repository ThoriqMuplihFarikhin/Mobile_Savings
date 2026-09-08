<?php

use App\Livewire\Admin\AntrianKomplain;
use App\Models\Komplain;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

it('allows nasabah to submit a complaint', function () {
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

    $this->actingAs($nasabah);

    Livewire::test(App\Livewire\Nasabah\Komplain::class)
        ->call('toggleForm')
        ->set('kategori', 'saldo')
        ->set('deskripsi', 'Saldo saya tidak sesuai dengan catatan setoran kemarin')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('komplain', [
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'status' => 'baru',
    ]);
});

it('rejects complaint with deskripsi too short', function () {
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

    $this->actingAs($nasabah);

    Livewire::test(App\Livewire\Nasabah\Komplain::class)
        ->call('toggleForm')
        ->set('kategori', 'saldo')
        ->set('deskripsi', 'Saldo')
        ->call('submit')
        ->assertHasErrors(['deskripsi']);
});

it('allows admin to process complaint', function () {
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

    $komplain = Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya tidak sesuai dengan catatan setoran kemarin',
        'status' => 'baru',
        'tanggal_dibuat' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(AntrianKomplain::class)
        ->call('proses', $komplain->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('komplain', [
        'id' => $komplain->id,
        'status' => 'diproses',
    ]);
});

it('allows admin to complete complaint with catatan', function () {
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

    $komplain = Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya tidak sesuai dengan catatan setoran kemarin',
        'status' => 'diproses',
        'tanggal_dibuat' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(AntrianKomplain::class)
        ->set('catatan', 'Sudah dikoreksi, saldo telah disesuaikan')
        ->call('selesai', $komplain->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('komplain', [
        'id' => $komplain->id,
        'status' => 'selesai',
        'catatan_penyelesaian' => 'Sudah dikoreksi, saldo telah disesuaikan',
        'ditangani_oleh' => $admin->id,
    ]);
});

it('rejects complaint completion without catatan', function () {
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

    $komplain = Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya tidak sesuai dengan catatan setoran kemarin',
        'status' => 'diproses',
        'tanggal_dibuat' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(AntrianKomplain::class)
        ->set('catatan', '')
        ->call('selesai', $komplain->id)
        ->assertHasErrors(['catatan']);

    $this->assertDatabaseHas('komplain', [
        'id' => $komplain->id,
        'status' => 'diproses',
    ]);
});

it('allows nasabah to submit complaint with related transaction', function () {
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

    $transaksi = TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->subDays(2),
        'tanggal_input_sistem' => now()->subDays(2),
        'input_by' => $nasabah->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $this->actingAs($nasabah);

    Livewire::test(App\Livewire\Nasabah\Komplain::class)
        ->call('toggleForm')
        ->set('kategori', 'saldo')
        ->set('transaksiTerkaitId', $transaksi->id)
        ->set('deskripsi', 'Saldo saya tidak sesuai dengan catatan setoran kemarin')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('komplain', [
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'transaksi_terkait_id' => $transaksi->id,
        'status' => 'baru',
    ]);
});

it('sends notification to nasabah when admin processes complaint', function () {
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

    $komplain = Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya tidak sesuai dengan catatan setoran kemarin',
        'status' => 'baru',
        'tanggal_dibuat' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(AntrianKomplain::class)
        ->call('proses', $komplain->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('komplain', [
        'id' => $komplain->id,
        'status' => 'diproses',
    ]);

    $this->assertDatabaseHas('log_notifikasi', [
        'nasabah_id' => $nasabah->id,
        'judul' => 'Komplain Diproses',
        'channel' => 'in_app',
    ]);
});
