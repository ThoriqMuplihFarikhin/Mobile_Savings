<?php

use App\Livewire\Admin\RegistrasiNasabah;
use App\Livewire\Admin\Settings\Security;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\User;
use Livewire\Livewire;

it('redirects nasabah with harus_ganti_pin to security page', function () {
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);

    $this->actingAs($nasabah)
        ->get(route('nasabah.pengaturan.index'))
        ->assertRedirect(route('security.edit'));
});

it('redirects kolektor with harus_ganti_pin to security page', function () {
    $kolektor = User::factory()->kolektor()->create(['harus_ganti_pin' => true]);

    $this->actingAs($kolektor)
        ->get(route('kolektor.pengaturan.index'))
        ->assertRedirect(route('security.edit'));
});

it('redirects admin with harus_ganti_pin to security page', function () {
    $admin = User::factory()->admin()->create(['harus_ganti_pin' => true]);

    $this->actingAs($admin)
        ->get(route('admin.pengaturan.index'))
        ->assertRedirect(route('security.edit'));
});

it('allows access to security page when harus_ganti_pin is true', function () {
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);

    $this->actingAs($nasabah)
        ->get(route('security.edit'))
        ->assertOk();
});

it('clears harus_ganti_pin after successful PIN change', function () {
    $nasabah = User::factory()->nasabah()->create([
        'pin_hash' => Hash::make('111111'),
        'harus_ganti_pin' => true,
    ]);

    $this->actingAs($nasabah);

    Livewire::test(Security::class)
        ->set('current_pin', '111111')
        ->set('pin', '222222')
        ->set('pin_confirmation', '222222')
        ->call('updatePin')
        ->assertHasNoErrors();

    $nasabah->refresh();
    expect($nasabah->harus_ganti_pin)->toBeFalse();
});

it('does not redirect when harus_ganti_pin is false', function () {
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => false]);

    $this->actingAs($nasabah)
        ->get(route('nasabah.pengaturan.index'))
        ->assertOk();
});

it('generates random PIN for nasabah registered by admin', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(RegistrasiNasabah::class)
        ->set('nama', 'Nasabah Baru')
        ->set('noHp', '081234567890')
        ->set('alamat', 'Jl. Test No. 1')
        ->set('tanggalLahir', '1990-01-01')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();

    $user = User::where('no_hp', '081234567890')->first();
    expect($user)->not->toBeNull();
    expect($user->harus_ganti_pin)->toBeTrue();
    expect($user->pin_hash)->not->toBe(Hash::make('123456'));
});

it('generates random PIN for nasabah registered by kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(DaftarNasabah::class)
        ->set('nama', 'Nasabah Kolektor')
        ->set('noHp', '081234567891')
        ->set('alamat', 'Jl. Test No. 2')
        ->set('tanggalLahir', '1990-01-01')
        ->set('jenisKelamin', 'perempuan')
        ->call('submit')
        ->assertHasNoErrors();

    $user = User::where('no_hp', '081234567891')->first();
    expect($user)->not->toBeNull();
    expect($user->harus_ganti_pin)->toBeTrue();
    expect($user->pin_hash)->not->toBe(Hash::make('123456'));
});
