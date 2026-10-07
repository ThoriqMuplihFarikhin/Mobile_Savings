<?php

use App\Livewire\Admin\ManajemenNasabah;
use App\Livewire\Admin\ManajemenProduk;
use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\SerahTerimaPaket;
use App\Models\User;
use Livewire\Livewire;

it('menukarkan tabel monitoring dan manajemen menjadi kartu', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $daftar = [
        MonitoringSetoran::class,
        ManajemenNasabah::class,
        ManajemenProduk::class,
    ];

    foreach ($daftar as $kelas) {
        $html = Livewire::test($kelas)->html();

        expect(substr_count($html, 'hidden md:table'))->toBe(1);
        expect(substr_count($html, 'data-test="kartu-tabel"'))->toBe(1);
        expect($html)->toContain('data-test="kartu-tabel" class="divide-y divide-[#ebebeb] md:hidden');
        expect($html)->toContain('min-h-11');
    }
});

it('memberi tinggi aksi 44px pada serah terima paket yang sudah berbentuk kartu', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $html = Livewire::test(SerahTerimaPaket::class)->html();

    expect(substr_count($html, '<table'))->toBe(0);
    expect($html)->toContain('min-h-11');
});
