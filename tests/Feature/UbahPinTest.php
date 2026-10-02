<?php

use App\Livewire\Admin\Settings\Security;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

it('menyetel harus_ganti_pin false pada halaman volt dan komponen admin', function () {
    $volt = User::factory()->nasabah()->create([
        'pin_hash' => Hash::make('111111'),
        'harus_ganti_pin' => true,
        'percobaan_gagal' => 3,
    ]);

    $this->actingAs($volt);

    Livewire::test('pages::settings.security')
        ->set('current_pin', '111111')
        ->set('pin', '428193')
        ->set('pin_confirmation', '428193')
        ->call('updatePin')
        ->assertHasNoErrors();

    $volt->refresh();
    expect($volt->harus_ganti_pin)->toBeFalse()
        ->and(Hash::check('428193', $volt->pin_hash))->toBeTrue()
        ->and($volt->percobaan_gagal)->toBe(0)
        ->and(LogAktivitas::where('user_id', $volt->id)->where('aksi', 'ubah_pin')->exists())->toBeTrue();

    $admin = User::factory()->admin()->create([
        'pin_hash' => Hash::make('333333'),
        'harus_ganti_pin' => true,
    ]);

    $this->actingAs($admin);

    Livewire::test(Security::class)
        ->set('current_pin', '333333')
        ->set('pin', '705164')
        ->set('pin_confirmation', '705164')
        ->call('updatePin')
        ->assertHasNoErrors();

    $admin->refresh();
    expect($admin->harus_ganti_pin)->toBeFalse();
});

it('menolak pin baru yang sama dengan pin lama', function () {
    $nasabah = User::factory()->nasabah()->create(['pin_hash' => Hash::make('111111')]);

    $this->actingAs($nasabah);

    foreach (['pages::settings.security', Security::class] as $komponen) {
        Livewire::test($komponen)
            ->set('current_pin', '111111')
            ->set('pin', '111111')
            ->set('pin_confirmation', '111111')
            ->call('updatePin')
            ->assertHasErrors('pin');
    }

    expect(Hash::check('111111', $nasabah->fresh()->pin_hash))->toBeTrue();
});

it('menolak pin lemah', function () {
    $nasabah = User::factory()->nasabah()->create(['pin_hash' => Hash::make('111111')]);

    $this->actingAs($nasabah);

    foreach (['pages::settings.security', Security::class] as $komponen) {
        Livewire::test($komponen)
            ->set('current_pin', '111111')
            ->set('pin', '123456')
            ->set('pin_confirmation', '123456')
            ->call('updatePin')
            ->assertHasErrors('pin');
    }

    expect(Hash::check('123456', $nasabah->fresh()->pin_hash))->toBeFalse();
});

it('memblokir percobaan ganti pin setelah 5 kali pin salah', function () {
    $nasabah = User::factory()->nasabah()->create(['pin_hash' => Hash::make('111111')]);

    $this->actingAs($nasabah);

    $komponen = Livewire::test('pages::settings.security');

    for ($i = 0; $i < 5; $i++) {
        $komponen
            ->set('current_pin', '999999')
            ->set('pin', '428193')
            ->set('pin_confirmation', '428193')
            ->call('updatePin')
            ->assertHasErrors('current_pin');
    }

    $komponen
        ->set('current_pin', '111111')
        ->set('pin', '428193')
        ->set('pin_confirmation', '428193')
        ->call('updatePin')
        ->assertHasErrors('current_pin');

    expect(Hash::check('111111', $nasabah->fresh()->pin_hash))->toBeTrue();
});

it('tidak mengalihkan ke halaman ganti pin setelah pin diperbarui', function () {
    $nasabah = User::factory()->nasabah()->create([
        'pin_hash' => Hash::make('111111'),
        'harus_ganti_pin' => true,
    ]);

    $this->actingAs($nasabah)
        ->get(route('dashboard'))
        ->assertRedirect(route('security.edit'));

    Livewire::test('pages::settings.security')
        ->set('current_pin', '111111')
        ->set('pin', '428193')
        ->set('pin_confirmation', '428193')
        ->call('updatePin')
        ->assertHasNoErrors();

    $this->actingAs($nasabah->fresh())
        ->get(route('dashboard'))
        ->assertOk();
});
