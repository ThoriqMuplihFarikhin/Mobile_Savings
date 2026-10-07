<?php

use App\Models\LogAktivitas;
use App\Models\User;
use App\Support\PortalLogin;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Testing\TestResponse;

/**
 * POST login ke portal tertentu (tanpa CSRF mengikuti pola AuthenticationTest).
 */
function kirimLoginPortal(object $testCase, string $noHp, string $pin, string $portal): TestResponse
{
    return $testCase->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $noHp,
            'password' => $pin,
            'portal' => $portal,
        ]);
}

it('matriks 3 role x 3 portal: hanya diagonal login yang berhasil', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $users = [
        'nasabah' => User::factory()->nasabah()->create([
            'no_hp' => '081111111111',
            'pin_hash' => bcrypt('123456'),
        ]),
        'kolektor' => User::factory()->kolektor()->create([
            'no_hp' => '082222222222',
            'pin_hash' => bcrypt('123456'),
        ]),
        'admin' => User::factory()->admin()->create([
            'no_hp' => '083333333333',
            'pin_hash' => bcrypt('123456'),
        ]),
    ];

    foreach (['nasabah', 'kolektor', 'admin'] as $portal) {
        foreach ($users as $role => $user) {
            $response = kirimLoginPortal($this, $user->no_hp, '123456', $portal);

            if ($role === $portal) {
                $response->assertRedirect(route('dashboard', absolute: false));
                expect(auth()->id())->toBe($user->id);

                $this->post(route('logout'));
                $this->assertGuest();
            } else {
                $response->assertSessionHasErrorsIn('no_hp');
                $this->assertGuest();
            }
        }
    }
});

it('mencatat login_portal_salah tanpa membocorkan pesan berbeda', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $admin = User::factory()->admin()->create([
        'no_hp' => '084444444444',
        'pin_hash' => bcrypt('123456'),
    ]);

    $salahPortal = kirimLoginPortal($this, $admin->no_hp, '123456', 'nasabah');
    $salahPortal->assertSessionHasErrorsIn('no_hp');
    $this->assertGuest();

    expect(LogAktivitas::where('aksi', 'login_portal_salah')
        ->where('entitas_id', $admin->id)
        ->exists())->toBeTrue();

    $pesanSalahPortal = (string) session('errors')->first('no_hp');

    $salahPin = kirimLoginPortal($this, $admin->no_hp, '999999', 'admin');
    $salahPin->assertSessionHasErrorsIn('no_hp');
    $this->assertGuest();

    $pesanSalahPin = (string) session('errors')->first('no_hp');

    expect($pesanSalahPortal)->not->toBe('')
        ->and($pesanSalahPortal)->toBe($pesanSalahPin);
});

it('menyediakan halaman login ketiga portal dengan nilai portal tersembunyi', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('value="nasabah"', false);

    $this->get(route('login.kolektor'))
        ->assertOk()
        ->assertSee('value="kolektor"', false);

    $this->get(route('login.admin'))
        ->assertOk()
        ->assertSee('value="admin"', false);
});

it('logout kembali ke portal login yang sama', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $admin = User::factory()->admin()->create([
        'no_hp' => '085555555555',
        'pin_hash' => bcrypt('123456'),
    ]);

    kirimLoginPortal($this, $admin->no_hp, '123456', 'admin')
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertCookie(PortalLogin::COOKIE, 'admin');

    $this->withCookie(PortalLogin::COOKIE, 'admin')
        ->post(route('logout'))
        ->assertRedirect(route('login.admin'));

    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => '086666666666',
        'pin_hash' => bcrypt('123456'),
    ]);

    kirimLoginPortal($this, $nasabah->no_hp, '123456', 'nasabah')
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertCookie(PortalLogin::COOKIE, 'nasabah');

    $this->withCookie(PortalLogin::COOKIE, 'nasabah')
        ->post(route('logout'))
        ->assertRedirect(route('login'));
});

it('akun terkunci diarahkan ke portal login sesuai perannya', function () {
    $admin = User::factory()->admin()->create(['status_akun' => 'terkunci']);
    $kolektor = User::factory()->kolektor()->create(['status_akun' => 'terkunci']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('login.admin'));

    $this->actingAs($kolektor)
        ->get(route('dashboard'))
        ->assertRedirect(route('login.kolektor'));
});

it('kunci sementara 15 menit tetap terpicu melalui portal kolektor', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $kolektor = User::factory()->kolektor()->create([
        'no_hp' => '089900000001',
        'pin_hash' => bcrypt('123456'),
    ]);

    for ($i = 0; $i < 5; $i++) {
        kirimLoginPortal($this, $kolektor->no_hp, '999999', 'kolektor')
            ->assertSessionHasErrorsIn('no_hp');

        $this->assertGuest();
    }

    expect($kolektor->fresh()->login_terkunci_hingga)->not->toBeNull();

    $this->travel(2)->minutes();

    kirimLoginPortal($this, $kolektor->no_hp, '123456', 'kolektor')
        ->assertSessionHasErrorsIn('no_hp');

    $this->assertGuest();
});

it('akun offline ditolak di semua portal', function () {
    $this->withoutMiddleware(PreventRequestForgery::class);

    $offline = User::factory()->nasabah()->create([
        'no_hp' => '087777777777',
        'pin_hash' => bcrypt('123456'),
        'mode_akses' => 'offline',
    ]);

    foreach (['nasabah', 'kolektor', 'admin'] as $portal) {
        kirimLoginPortal($this, $offline->no_hp, '123456', $portal)
            ->assertSessionHasErrorsIn('no_hp');

        $this->assertGuest();
    }
});
