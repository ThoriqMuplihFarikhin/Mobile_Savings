<?php

use App\Livewire\Admin\AntrianKomplain;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\HandoverKolektor;
use App\Livewire\Admin\VerifikasiNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

function buatPenarikanP74b(User $admin, User $nasabah): TransaksiPenarikan
{
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan P74b',
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

it('menukarkan tabel antrean persetujuan menjadi kartu dengan tombol aksi besar', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $daftar = [
        ApprovalPenarikan::class,
        VerifikasiNasabah::class,
        AntrianKomplain::class,
        HandoverKolektor::class,
    ];

    foreach ($daftar as $kelas) {
        $html = Livewire::test($kelas)->html();

        expect(substr_count($html, 'hidden md:table'))->toBe(1);
        expect(substr_count($html, 'data-test="kartu-tabel"'))->toBe(1);
        expect($html)->toContain('data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden');
        expect($html)->toContain('min-h-11');
    }
});

it('menjaga wire:confirm pada aksi uang approval penarikan', function () {
    $admin = User::factory()->admin()->create();
    buatPenarikanP74b($admin, User::factory()->nasabah()->create());

    $this->actingAs($admin);

    $html = Livewire::test(ApprovalPenarikan::class)->html();

    expect($html)->toContain('wire:confirm');
});
