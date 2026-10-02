<?php

use App\Livewire\Admin\DetailNasabah;
use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\ManajemenNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\User;
use App\Support\Pin;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: User, 2: NasabahProfil}
 */
function fixtureResetPinNasabah(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'pin_hash' => bcrypt('111111'),
        'harus_ganti_pin' => false,
        'percobaan_gagal' => 4,
        'login_terkunci_hingga' => now()->addMinutes(5),
    ]);
    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return [$admin, $nasabah, $profil];
}

it('admin mereset pin nasabah memunculkan pin baru sekali lalu memaksa ganti pin', function () {
    [$admin, $nasabah, $profil] = fixtureResetPinNasabah();

    DB::table('sessions')->insert([
        'id' => 'sesi-nasabah-aktif',
        'user_id' => $nasabah->id,
        'ip_address' => '127.0.0.1',
        'user_agent' => 'pest',
        'payload' => base64_encode(serialize([])),
        'last_activity' => now()->timestamp,
    ]);

    $halaman = Livewire::actingAs($admin)->test(ManajemenNasabah::class);
    $halaman->call('confirmResetPin', $nasabah->id)
        ->assertSet('confirmResetPin', true)
        ->call('resetPin')
        ->assertSet('confirmResetPin', false);

    expect(preg_match('/PIN baru: (\d{6})/', $halaman->html(), $cocok))->toBe(1);

    $pinBaru = $cocok[1];
    expect(Pin::lemah($pinBaru))->toBeFalse();

    $nasabah->refresh();
    expect(Hash::check($pinBaru, $nasabah->pin_hash))->toBeTrue()
        ->and($nasabah->harus_ganti_pin)->toBeTrue()
        ->and($nasabah->percobaan_gagal)->toBe(0)
        ->and($nasabah->login_terkunci_hingga)->toBeNull();

    expect(DB::table('sessions')->where('id', 'sesi-nasabah-aktif')->exists())->toBeFalse();

    $log = LogAktivitas::where('aksi', 'reset_pin')
        ->where('entitas_terkait', 'users')
        ->where('entitas_id', $nasabah->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and(json_encode($log->detail))->not->toMatch('/(?<!\d)\d{6}(?!\d)/');

    expect($profil->user_id)->toBe($nasabah->id);
});

it('pin lama tidak bisa login setelah reset dan pin baru diterima', function () {
    [$admin, $nasabah] = fixtureResetPinNasabah();

    $halaman = Livewire::actingAs($admin)->test(ManajemenNasabah::class);
    $halaman->call('confirmResetPin', $nasabah->id)
        ->call('resetPin');

    expect(preg_match('/PIN baru: (\d{6})/', $halaman->html(), $cocok))->toBe(1);
    $pinBaru = $cocok[1];

    $this->app['auth']->guard('web')->logout();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), ['no_hp' => $nasabah->no_hp, 'password' => '111111'])
        ->assertSessionHasErrorsIn('no_hp');
    $this->assertGuest();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), ['no_hp' => $nasabah->no_hp, 'password' => $pinBaru])
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();
    expect($nasabah->refresh()->harus_ganti_pin)->toBeTrue();
});

it('admin mereset pin kolektor dari kelola kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create([
        'pin_hash' => bcrypt('111111'),
        'harus_ganti_pin' => false,
        'percobaan_gagal' => 2,
    ]);

    $halaman = Livewire::actingAs($admin)->test(KelolaKolektor::class);
    $halaman->call('confirmResetPin', $kolektor->id)
        ->call('resetPin');

    expect(preg_match('/PIN baru: (\d{6})/', $halaman->html(), $cocok))->toBe(1);
    $kolektor->refresh();

    expect(Hash::check($cocok[1], $kolektor->pin_hash))->toBeTrue()
        ->and($kolektor->harus_ganti_pin)->toBeTrue()
        ->and($kolektor->percobaan_gagal)->toBe(0)
        ->and($kolektor->login_terkunci_hingga)->toBeNull();
});

it('admin mereset pin nasabah dari halaman detail', function () {
    [$admin, $nasabah] = fixtureResetPinNasabah();

    $halaman = Livewire::actingAs($admin)->test(DetailNasabah::class, ['user' => $nasabah]);
    $halaman->call('confirmResetPin')
        ->assertSet('confirmResetPin', true)
        ->call('resetPin')
        ->assertSet('confirmResetPin', false);

    expect(preg_match('/PIN baru: (\d{6})/', $halaman->html()))->toBe(1);

    $nasabah->refresh();
    expect($nasabah->harus_ganti_pin)->toBeTrue()
        ->and(Hash::check('111111', $nasabah->pin_hash))->toBeFalse();
});

it('non-admin ditolak memuat komponen reset pin', function () {
    $kolektor = User::factory()->kolektor()->create();

    Livewire::actingAs($kolektor)
        ->test(ManajemenNasabah::class)
        ->assertForbidden();

    $nasabah = User::factory()->nasabah()->create();

    Livewire::actingAs($nasabah)
        ->test(KelolaKolektor::class)
        ->assertForbidden();
});

it('mereset pin admin lain ditolak', function () {
    $admin = User::factory()->admin()->create();
    $adminLain = User::factory()->admin()->create(['pin_hash' => bcrypt('111111')]);

    Livewire::actingAs($admin)
        ->test(ManajemenNasabah::class)
        ->call('confirmResetPin', $adminLain->id)
        ->call('resetPin')
        ->assertHasErrors('reset_pin');

    expect(Hash::check('111111', $adminLain->refresh()->pin_hash))->toBeTrue()
        ->and(LogAktivitas::where('aksi', 'reset_pin')->exists())->toBeFalse();
});
