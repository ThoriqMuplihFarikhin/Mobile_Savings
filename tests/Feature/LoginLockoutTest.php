<?php

use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\ManajemenNasabah;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Livewire\Livewire;

function kirimPercobaanLogin(object $testCase, string $noHp, string $pin)
{
    return $testCase->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $noHp,
            'password' => $pin,
        ]);
}

function lockUserForLoginTest(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
        'percobaan_gagal' => 0,
    ], $attributes));
}

it('mengunci login sementara 15 menit setelah 5 pin salah tanpa mengunci akun permanen', function () {
    $user = lockUserForLoginTest();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, 'pin-salah');
        $this->assertGuest();
    }

    $user->refresh();

    expect($user->status_akun)->toBe('aktif')
        ->and($user->login_terkunci_hingga)->not->toBeNull()
        ->and($user->login_terkunci_hingga->isFuture())->toBeTrue()
        ->and($user->percobaan_gagal)->toBe(0);
});

it('menolak login dengan pin benar selama masa kunci sementara berlaku', function () {
    $user = lockUserForLoginTest();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, 'pin-salah');
    }

    $this->travel(2)->minutes();

    kirimPercobaanLogin($this, $user->no_hp, '123456');
    $this->assertGuest();
});

it('mengizinkan login kembali setelah masa kunci sementara berakhir', function () {
    $user = lockUserForLoginTest();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, 'pin-salah');
    }

    $this->travel(16)->minutes();

    kirimPercobaanLogin($this, $user->no_hp, '123456')->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

it('akun admin juga hanya terkunci sementara oleh percobaan pin salah', function () {
    $admin = lockUserForLoginTest(['role' => 'admin']);

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $admin->no_hp, 'pin-salah');
    }

    $this->travel(16)->minutes();

    kirimPercobaanLogin($this, $admin->no_hp, '123456')->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

it('menolak login untuk nomor yang tidak terdaftar', function () {
    $response = kirimPercobaanLogin($this, '089999999999', '123456');

    $response->assertSessionHasErrorsIn('no_hp');
    $this->assertGuest();
});

it('command darurat akun:buka-kunci mereset kunci akun', function () {
    $user = lockUserForLoginTest([
        'status_akun' => 'terkunci',
        'percobaan_gagal' => 5,
        'login_terkunci_hingga' => now()->addMinutes(10),
    ]);

    $this->artisan('akun:buka-kunci', ['no_hp' => $user->no_hp])->assertExitCode(0);

    $user->refresh();
    expect($user->status_akun)->toBe('aktif')
        ->and($user->percobaan_gagal)->toBe(0)
        ->and($user->login_terkunci_hingga)->toBeNull();

    kirimPercobaanLogin($this, $user->no_hp, '123456')->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

it('command darurat akun:buka-kunci gagal untuk nomor yang tidak terdaftar', function () {
    $this->artisan('akun:buka-kunci', ['no_hp' => '089999999999'])->assertExitCode(1);
});

it('admin membuka kunci akun kolektor dari kelola kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create([
        'status_akun' => 'terkunci',
        'percobaan_gagal' => 5,
        'login_terkunci_hingga' => now()->addMinutes(10),
    ]);

    Livewire::actingAs($admin)
        ->test(KelolaKolektor::class)
        ->call('bukaKunci', $kolektor->id);

    $kolektor->refresh();
    expect($kolektor->status_akun)->toBe('aktif')
        ->and($kolektor->percobaan_gagal)->toBe(0)
        ->and($kolektor->login_terkunci_hingga)->toBeNull();
});

it('admin membuka kunci akun nasabah aktif dari manajemen nasabah', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'status_akun' => 'terkunci',
        'percobaan_gagal' => 5,
        'login_terkunci_hingga' => now()->addMinutes(10),
    ]);
    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    Livewire::actingAs($admin)
        ->test(ManajemenNasabah::class)
        ->call('bukaKunci', $profil->id);

    $nasabah->refresh();
    expect($nasabah->status_akun)->toBe('aktif')
        ->and($nasabah->percobaan_gagal)->toBe(0)
        ->and($nasabah->login_terkunci_hingga)->toBeNull();
});

it('tidak membuka kunci nasabah yang belum diverifikasi', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create([
        'status_akun' => 'terkunci',
        'percobaan_gagal' => 5,
        'login_terkunci_hingga' => now()->addMinutes(10),
    ]);
    $profil = NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'pending_verifikasi',
    ]);

    Livewire::actingAs($admin)
        ->test(ManajemenNasabah::class)
        ->call('bukaKunci', $profil->id);

    $nasabah->refresh();
    expect($nasabah->status_akun)->toBe('terkunci')
        ->and($nasabah->login_terkunci_hingga)->not->toBeNull();
});
