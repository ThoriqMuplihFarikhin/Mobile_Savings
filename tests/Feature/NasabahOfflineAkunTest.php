<?php

use App\Livewire\Admin\RegistrasiNasabah;
use App\Livewire\Admin\VerifikasiNasabah;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

function konfigurasiWaP22(): void
{
    config([
        'services.whatsapp.url' => 'https://wa.example.test/send',
        'services.whatsapp.token' => 'token-tes-p22',
    ]);
}

function nasabahOfflineP22(array $atribut = []): User
{
    return User::factory()->nasabah()->create(array_merge([
        'no_hp' => '081234567890',
        'pin_hash' => bcrypt('123456'),
        'mode_akses' => 'offline',
        'percobaan_gagal' => 0,
    ], $atribut));
}

function pesanGalatLogin(User $user, string $pin): string
{
    $response = test()->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $user->no_hp,
            'password' => $pin,
        ]);

    $response->assertSessionHasErrors('no_hp');

    return (string) session('errors')->first('no_hp');
}

it('menolak login nasabah offline dengan pesan generik pin salah', function () {
    $offline = nasabahOfflineP22();
    $digital = User::factory()->nasabah()->create([
        'no_hp' => '081234567891',
        'pin_hash' => bcrypt('123456'),
    ]);

    $pesanOffline = pesanGalatLogin($offline, '123456');
    $this->assertGuest();

    $pesanPinSalah = pesanGalatLogin($digital, '999999');
    $this->assertGuest();

    expect($pesanOffline)->toBe($pesanPinSalah)
        ->and($offline->fresh()->percobaan_gagal)->toBe(0)
        ->and($offline->fresh()->login_terkunci_hingga)->toBeNull();
});

it('menolak login offline meski punya no_hp dan pin yang benar', function () {
    $offline = nasabahOfflineP22();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $offline->no_hp,
            'password' => '123456',
        ])
        ->assertSessionHasErrors('no_hp');

    $this->assertGuest();
});

it('registrasi admin mode offline membuat akun tanpa hp dan tanpa antrean whatsapp', function () {
    Queue::fake();
    konfigurasiWaP22();

    $admin = User::factory()->admin()->create();

    $halaman = Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->set('modeOffline', true)
        ->set('nama', 'Nasabah Offline Admin')
        ->set('alamat', 'Jl. Offline Admin No. 1')
        ->set('tanggalLahir', '1992-04-04')
        ->set('jenisKelamin', 'perempuan')
        ->call('submit')
        ->assertHasNoErrors();

    $user = User::where('name', 'Nasabah Offline Admin')->firstOrFail();

    Queue::assertNothingPushed();

    expect($user->no_hp)->toBeNull()
        ->and($user->mode_akses)->toBe('offline')
        ->and($user->harus_ganti_pin)->toBeFalse()
        ->and(Hash::check('123456', $user->pin_hash))->toBeFalse()
        ->and(LogNotifikasi::count())->toBe(0)
        ->and($user->nasabahProfil->status_pendaftaran)->toBe('aktif');

    $halaman->assertSee('Nasabah offline berhasil didaftarkan!');
});

it('registrasi kolektor mode offline menunggu verifikasi tanpa hp', function () {
    Queue::fake();
    konfigurasiWaP22();

    $kolektor = User::factory()->kolektor()->create();

    Livewire::actingAs($kolektor)
        ->test(DaftarNasabah::class)
        ->set('modeOffline', true)
        ->set('nama', 'Nasabah Offline Kolektor')
        ->set('alamat', 'Jl. Offline Kolektor No. 1')
        ->set('tanggalLahir', '1991-03-03')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();

    $profil = NasabahProfil::where('nama', 'Nasabah Offline Kolektor')->firstOrFail();
    $user = $profil->user;

    Queue::assertNothingPushed();

    expect($user->no_hp)->toBeNull()
        ->and($user->mode_akses)->toBe('offline')
        ->and($profil->status_pendaftaran)->toBe('pending_verifikasi')
        ->and(LogNotifikasi::count())->toBe(0);
});

it('registrasi digital tetap mewajibkan no hp', function () {
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->set('modeOffline', false)
        ->set('nama', 'Nasabah Digital')
        ->set('alamat', 'Jl. Digital No. 1')
        ->set('tanggalLahir', '1993-02-02')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasErrors('noHp');
});

it('toggle mode offline menyembunyikan field no hp di form registrasi', function () {
    $admin = User::factory()->admin()->create();

    $halaman = Livewire::actingAs($admin)
        ->test(RegistrasiNasabah::class)
        ->call('toggleForm');

    $halaman->assertSee('08xxxxxxxxxx')
        ->set('modeOffline', true)
        ->assertDontSee('08xxxxxxxxxx');
});

it('verifikasi admin nasabah offline tidak mengirim wa dan tidak menampilkan pin', function () {
    Queue::fake();
    konfigurasiWaP22();

    $kolektor = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($kolektor)
        ->test(DaftarNasabah::class)
        ->set('modeOffline', true)
        ->set('nama', 'Nasabah Offline Verifikasi')
        ->set('alamat', 'Jl. Offline Verifikasi No. 1')
        ->set('tanggalLahir', '1990-01-01')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors();

    $profil = NasabahProfil::where('nama', 'Nasabah Offline Verifikasi')->firstOrFail();

    $halaman = Livewire::actingAs($admin)
        ->test(VerifikasiNasabah::class)
        ->call('approve', $profil->id);

    Queue::assertNothingPushed();

    expect($profil->fresh()->status_pendaftaran)->toBe('aktif')
        ->and(LogNotifikasi::count())->toBe(0)
        ->and($halaman->html())->not->toMatch('/PIN\s*(?:awal)?:\s*\d{6}/');
});
