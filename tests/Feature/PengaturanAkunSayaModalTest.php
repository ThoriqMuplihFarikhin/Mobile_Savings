<?php

use App\Livewire\Admin\Pengaturan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('menampilkan navigasi akun saya dan dapat membuka modal profil', function () {
    $admin = User::factory()->admin()->create([
        'name' => 'Admin Utama',
        'no_hp' => '081234567890',
    ]);

    Livewire::actingAs($admin)
        ->test(Pengaturan::class)
        ->call('setTab', 'akun')
        ->assertSee('Edit Profil Saya')
        ->assertSee('Ganti PIN Saya')
        ->assertSee('Tampilan (Terang/Gelap)')
        ->call('bukaModalProfil')
        ->assertSet('showModalProfil', true)
        ->assertSet('profilNama', 'Admin Utama')
        ->assertSet('profilNoHp', '081234567890')
        ->set('profilNama', 'Admin Diperbarui')
        ->call('simpanProfil')
        ->assertSet('showModalProfil', false);

    expect($admin->fresh()->name)->toBe('Admin Diperbarui');
});

it('dapat membuka modal pin dan memperbarui pin keamanan', function () {
    $admin = User::factory()->admin()->create([
        'pin_hash' => Hash::make('123456'),
    ]);

    Livewire::actingAs($admin)
        ->test(Pengaturan::class)
        ->call('setTab', 'akun')
        ->call('bukaModalPin')
        ->assertSet('showModalPin', true)
        ->set('current_pin', '123456')
        ->set('pin', '839174')
        ->set('pin_confirmation', '839174')
        ->call('simpanPin')
        ->assertSet('showModalPin', false);

    expect(Hash::check('839174', $admin->fresh()->pin_hash))->toBeTrue();
});

it('dapat membuka dan menutup modal preferensi tema', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Pengaturan::class)
        ->call('setTab', 'akun')
        ->call('bukaModalTema')
        ->assertSet('showModalTema', true)
        ->assertSee('Mode Terang')
        ->assertSee('Mode Gelap')
        ->assertSee('Ikuti Sistem')
        ->call('tutupModalTema')
        ->assertSet('showModalTema', false);
});
