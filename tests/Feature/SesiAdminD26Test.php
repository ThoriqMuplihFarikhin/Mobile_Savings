<?php

use App\Http\Middleware\AdminIdleTimeout;
use App\Livewire\Admin\KelolaKolektor;
use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Checksum;
use Livewire\Mechanisms\HandleRequests\HandleRequests;

/**
 * F2 / D26 — keamanan sesi admin: portal admin tanpa "Ingat saya" dan
 * batas idle yang mengeluarkan admin dari seluruh rute admin.
 */

/**
 * Nama cookie recaller Laravel: remember_{guard}_{sha1(guard class)}.
 */
function namaRecallerD26(): string
{
    return Auth::guard('web')->getRecallerName();
}

/**
 * POST login ke portal tertentu membawa flag remember (tanpa CSRF mengikuti
 * pola LoginPortalTerpisahD19Test).
 */
function kirimLoginIngatD26(object $testCase, string $noHp, string $pin, string $portal, string $remember): TestResponse
{
    return $testCase->withoutMiddleware(PreventRequestForgery::class)
        ->post(route('login.store'), [
            'no_hp' => $noHp,
            'password' => $pin,
            'portal' => $portal,
            'remember' => $remember,
        ]);
}

it('login portal admin tidak pernah membuat cookie remember (D26)', function () {
    $admin = User::factory()->admin()->create([
        'no_hp' => '081111111111',
        'pin_hash' => bcrypt('123456'),
    ]);

    kirimLoginIngatD26($this, $admin->no_hp, '123456', 'admin', 'on')
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertCookieMissing(namaRecallerD26());

    $this->assertAuthenticated();
});

it('login portal selain admin tetap menghormati remember (D26)', function () {
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => '082222222222',
        'pin_hash' => bcrypt('123456'),
    ]);

    kirimLoginIngatD26($this, $nasabah->no_hp, '123456', 'nasabah', 'on')
        ->assertRedirect(route('dashboard', absolute: false))
        ->assertCookie(namaRecallerD26());
});

it('halaman login admin menyembunyikan checkbox ingat saya (D26)', function () {
    $this->get(route('login.admin'))
        ->assertOk()
        ->assertDontSee('Ingat saya');

    $this->get(route('login.kolektor'))->assertOk()->assertSee('Ingat saya');
    $this->get(route('login'))->assertOk()->assertSee('Ingat saya');
});

it('permintaan admin yang aktif lolos dan memperbarui stempel aktivitas (D26)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk();

    expect(session()->has('admin_last_activity'))->toBeTrue();
});

it('sesi admin berakhir setelah 30 menit tidak aktif (D26)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk();

    $this->travel(31)->minutes();

    $this->get(route('admin.persetujuan.index'))
        ->assertRedirect(route('login.admin'))
        ->assertSessionHas('idle_expired', true);

    $this->assertGuest();

    expect(LogAktivitas::where('aksi', 'sesi_admin_kedaluwarsa')
        ->where('entitas_terkait', 'users')
        ->where('entitas_id', $admin->id)
        ->exists())->toBeTrue();
});

it('batas idle mengikuti konfigurasi auth.admin_idle_minutes (D26)', function () {
    expect(config('auth.admin_idle_minutes'))->toBe(30);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk();

    config(['auth.admin_idle_minutes' => 5]);

    $this->travel(6)->minutes();

    $this->get(route('admin.persetujuan.index'))
        ->assertRedirect(route('login.admin'));

    $this->assertGuest();
});

it('batas idle tidak berlaku untuk kolektor dan nasabah (D26)', function () {
    $pengguna = [
        User::factory()->kolektor()->create(),
        User::factory()->nasabah()->create(),
    ];

    foreach ($pengguna as $akun) {
        $this->actingAs($akun)->get(route('dashboard'))->assertOk();

        $this->travel(31)->minutes();

        $this->get(route('dashboard'))->assertOk();
        $this->assertAuthenticated();

        expect(session()->has('admin_last_activity'))->toBeFalse();
    }
});

it('halaman login menampilkan pesan sesi berakhir karena tidak aktif (D26)', function () {
    $this->withSession(['idle_expired' => true])
        ->get(route('login.admin'))
        ->assertOk()
        ->assertSee('Sesi berakhir karena tidak aktif');
});

it('middleware idle terdaftar sebagai persistent middleware Livewire (D26)', function () {
    expect(Livewire::getPersistentMiddleware())->toContain(AdminIdleTimeout::class);
});

it('permintaan update Livewire admin yang idle diarahkan ke login, bukan 419 (D26)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk();

    $snapshot = Livewire::test(KelolaKolektor::class)->snapshot;

    unset($snapshot['checksum']);
    $snapshot['memo']['path'] = '/admin/persetujuan';
    $snapshot['memo']['method'] = 'GET';
    $snapshot['checksum'] = Checksum::generate($snapshot);

    $this->travel(31)->minutes();

    $this->withoutMiddleware(PreventRequestForgery::class)
        ->json('post', app(HandleRequests::class)->getUpdateUri(), [
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => [],
                'calls' => [],
            ]],
        ], ['X-Livewire' => 'true'])
        ->assertRedirect(route('login.admin'));

    $this->assertGuest();
});
