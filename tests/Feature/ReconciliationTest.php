<?php

use App\Livewire\Admin\RekonsiliasiKas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

it('calculates total kas that should be held by kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
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

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 30000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id)
        ->assertViewHas('totalSeharusnya', 80000);
});

it('allows admin to reconcile with matching amount', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
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

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id)
        ->set('totalDiterima', 50000)
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('setoran_kolektor_kantor', [
        'kolektor_id' => $kolektor->id,
        'total_seharusnya' => 50000,
        'total_diterima' => 50000,
        'selisih' => 0,
        'status' => 'cocok',
    ]);

    $this->assertDatabaseHas('transaksi_setoran', [
        'input_by' => $kolektor->id,
        'sudah_disetor_ke_kantor' => true,
    ]);
});

it('requires keterangan when there is a selisih', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
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

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id)
        ->set('totalDiterima', 45000)
        ->call('submit');

    $this->assertDatabaseMissing('setoran_kolektor_kantor', [
        'kolektor_id' => $kolektor->id,
    ]);
});

it('records reconciliation with surplus and keterangan', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
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

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => false,
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->set('kolektorId', $kolektor->id)
        ->set('totalDiterima', 55000)
        ->set('keterangan', 'Kelebihan dari setoran kemarin')
        ->call('submit')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('setoran_kolektor_kantor', [
        'kolektor_id' => $kolektor->id,
        'total_seharusnya' => 50000,
        'total_diterima' => 55000,
        'selisih' => 5000,
        'status' => 'lebih',
        'keterangan_selisih' => 'Kelebihan dari setoran kemarin',
    ]);
});
