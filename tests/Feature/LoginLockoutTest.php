<?php

use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\ManajemenNasabah;
use App\Models\LogAktivitas;
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
        kirimPercobaanLogin($this, $user->no_hp, '999999');
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
        kirimPercobaanLogin($this, $user->no_hp, '999999');
    }

    $this->travel(2)->minutes();

    kirimPercobaanLogin($this, $user->no_hp, '123456');
    $this->assertGuest();
});

it('mengizinkan login kembali setelah masa kunci sementara berakhir', function () {
    $user = lockUserForLoginTest();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, '999999');
    }

    $this->travel(16)->minutes();

    kirimPercobaanLogin($this, $user->no_hp, '123456')->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();
});

it('akun admin juga hanya terkunci sementara oleh percobaan pin salah', function () {
    $admin = lockUserForLoginTest(['role' => 'admin']);

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $admin->no_hp, '999999');
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

it('menolak pin non-digit tanpa menaikkan percobaan_gagal', function () {
    $user = lockUserForLoginTest();

    kirimPercobaanLogin($this, $user->no_hp, 'pin-huruf!');
    $this->assertGuest();

    $user->refresh();
    expect($user->percobaan_gagal)->toBe(0)
        ->and($user->login_terkunci_hingga)->toBeNull();
});

it('kunci kedua lebih lama dari kunci pertama dan mencatat akun_terkunci_otomatis', function () {
    $user = lockUserForLoginTest();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, '999999');
    }
    $user->refresh();
    $durasiPertama = now()->diffInMinutes($user->login_terkunci_hingga);

    $this->travel(16)->minutes();

    for ($i = 0; $i < 5; $i++) {
        kirimPercobaanLogin($this, $user->no_hp, '999999');
    }
    $user->refresh();
    $durasiKedua = now()->diffInMinutes($user->login_terkunci_hingga);

    expect($durasiPertama)->toBeLessThan(30)
        ->and($durasiKedua)->toBeGreaterThan(30);

    $log = LogAktivitas::where('aksi', 'akun_terkunci_otomatis')
        ->where('user_id', $user->id)
        ->latest('id')
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->detail['kunci_ke'] ?? null)->toBe(2)
        ->and($log->detail['durasi_menit'] ?? null)->toBe(60);
});

it('limiter per-ip memblokir percobaan ke-21 ke nomor berbeda', function () {
    $response = null;

    for ($i = 1; $i <= 21; $i++) {
        $response = kirimPercobaanLogin($this, '0899'.str_pad((string) $i, 8, '0', STR_PAD_LEFT), '123456');
    }

    $response->assertStatus(429);
});

it('mencatat login_gagal dengan user_id akun target tanpa pin', function () {
    $user = lockUserForLoginTest();

    kirimPercobaanLogin($this, $user->no_hp, '999999');

    $log = LogAktivitas::where('aksi', 'login_gagal')
        ->where('user_id', $user->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and(json_encode($log->detail))->not->toContain('999999');
});
