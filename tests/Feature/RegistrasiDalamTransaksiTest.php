<?php

use App\Livewire\Admin\RegistrasiNasabah;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Livewire\Livewire;

function gagalBuatProfilP44(): void
{
    NasabahProfil::creating(function (): void {
        throw new RuntimeException('Pembuatan profil gagal untuk tes P44.');
    });
}

it('membatalkan pembuatan user saat profil gagal dibuat pada pendaftaran kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();

    gagalBuatProfilP44();

    try {
        Livewire::actingAs($kolektor)
            ->test(DaftarNasabah::class)
            ->set('nama', 'Nasabah Rollback')
            ->set('noHp', '081234567897')
            ->set('alamat', 'Jl. Rollback No. 1')
            ->set('tanggalLahir', '1990-01-01')
            ->set('jenisKelamin', 'laki-laki')
            ->call('submit');
    } catch (Throwable) {
        // exception model memang diizinkan menerus; yang diuji adalah rollback
    }

    expect(User::where('no_hp', '081234567897')->exists())->toBeFalse();
});

it('membatalkan pembuatan user saat profil gagal dibuat pada registrasi admin', function () {
    $admin = User::factory()->admin()->create();

    gagalBuatProfilP44();

    try {
        Livewire::actingAs($admin)
            ->test(RegistrasiNasabah::class)
            ->set('nama', 'Nasabah Rollback Admin')
            ->set('noHp', '081234567898')
            ->set('alamat', 'Jl. Rollback Admin No. 1')
            ->set('tanggalLahir', '1991-02-02')
            ->set('jenisKelamin', 'perempuan')
            ->call('submit');
    } catch (Throwable) {
        // exception model memang diizinkan menerus; yang diuji adalah rollback
    }

    expect(User::where('no_hp', '081234567898')->exists())->toBeFalse();
});
