<?php

use App\Helpers\ActivityLogger;
use App\Livewire\Admin\LogAktivitas;
use App\Models\User;
use Livewire\Livewire;

it('allows admin to access log aktivitas page', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/log')
        ->assertOk()
        ->assertSee('Log Aktivitas');
});

it('prevents non-admin from accessing log aktivitas page', function () {
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($nasabah)
        ->get('/admin/log')
        ->assertForbidden();
});

it('menampilkan log aktivitas dengan tabel desktop dan kartu mobile', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['name' => 'Siti Aminah']);

    $this->actingAs($admin);

    ActivityLogger::log('setor', 'transaksi_setoran', 1, [
        'nasabah_nama' => 'Siti Aminah',
        'nominal' => 20000,
    ], $admin->id);

    ActivityLogger::log('verifikasi_nasabah', 'nasabah_profil', 2, [
        'nama' => 'Lilis Suryani',
    ], $admin->id);

    $component = Livewire::test(LogAktivitas::class);

    $component->assertSee('Siti Aminah')
        ->assertSee('Setor')
        ->assertSee('Verifikasi')
        ->assertSee('hidden md:table', false)
        ->assertSee('data-test="kartu-tabel"', false);

    // Filter aksi
    $component->set('aksiFilter', 'setor')
        ->assertSee('Siti Aminah')
        ->assertDontSee('Lilis Suryani');

    // Reset filter and search
    $component->set('aksiFilter', '')
        ->set('search', 'Lilis')
        ->assertSee('Lilis Suryani')
        ->assertDontSee('Siti Aminah');
});
