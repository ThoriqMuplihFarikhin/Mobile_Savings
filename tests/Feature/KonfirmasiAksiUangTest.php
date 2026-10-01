<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function buatPenarikanT47(User $admin, User $nasabah): TransaksiPenarikan
{
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas T47',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return TransaksiPenarikan::create([
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
}

it('tombol setujui dan tolak penarikan punya wire:confirm', function () {
    $admin = User::factory()->admin()->create();
    buatPenarikanT47($admin, User::factory()->nasabah()->create());

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->assertSee('wire:confirm');
});

it('form koreksi dan batal setoran punya wire:confirm', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $penarikan = buatPenarikanT47($admin, $nasabah);
    $setoran = TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $penarikan->produk_id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $admin->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $this->actingAs($admin);

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->assertSee('wire:confirm');

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->assertSee('wire:confirm');
});

it('tombol konfirmasi proses rekonsiliasi kas punya wire:confirm', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $pengajuan = SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 150000,
        'status' => 'pending',
    ]);

    $this->actingAs($admin);

    Livewire::test(RekonsiliasiKas::class)
        ->call('startProcess', $pengajuan->id)
        ->assertSee('wire:confirm');
});
