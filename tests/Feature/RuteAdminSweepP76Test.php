<?php

use App\Models\NasabahProfil;
use App\Models\User;

function ruteAdminP76(): array
{
    return collect(app('router')->getRoutes()->getRoutes())
        ->filter(function ($route) {
            foreach ($route->gatherMiddleware() as $middleware) {
                $peran = null;
                if (str_starts_with($middleware, 'role:')) {
                    $peran = substr($middleware, 5);
                } elseif (($pos = strpos($middleware, 'RoleMiddleware:')) !== false) {
                    $peran = substr($middleware, $pos + 15);
                }

                if ($peran !== null) {
                    return explode('|', $peran) === ['admin'];
                }
            }

            return false;
        })
        ->values()
        ->all();
}

function uriAdminP76(array|object $route, User $nasabah): string
{
    return '/'.str_replace('{user}', (string) $nasabah->id, $route->uri());
}

function nasabahDetailP76(User $admin): User
{
    $nasabah = User::factory()->nasabah()->create();
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => $nasabah->name,
        'alamat' => 'Jalan Contoh',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    return $nasabah;
}

it('memberi respon 200 pada seluruh rute admin untuk role admin', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = nasabahDetailP76($admin);
    $this->actingAs($admin);

    $rute = ruteAdminP76();
    expect($rute)->not->toBeEmpty();

    foreach ($rute as $route) {
        $this->get(uriAdminP76($route, $nasabah))->assertOk();
    }
});

it('menolak kolektor pada seluruh rute admin dengan 403', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = nasabahDetailP76($admin);
    $this->actingAs(User::factory()->kolektor()->create());

    foreach (ruteAdminP76() as $route) {
        $this->get(uriAdminP76($route, $nasabah))->assertForbidden();
    }
});

it('menolak nasabah pada seluruh rute admin dengan 403', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = nasabahDetailP76($admin);
    $this->actingAs(User::factory()->nasabah()->create());

    foreach (ruteAdminP76() as $route) {
        $this->get(uriAdminP76($route, $nasabah))->assertForbidden();
    }
});

it('mengarahkan tamu ke login pada seluruh rute admin', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = nasabahDetailP76($admin);

    foreach (ruteAdminP76() as $route) {
        $this->get(uriAdminP76($route, $nasabah))->assertRedirect();
    }
});

it('menampilkan bottom-nav admin pada halaman admin melalui http', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertSee('data-test="bottom-nav-admin"', false)
        ->assertSee('data-test="tab-persetujuan"', false)
        ->assertSee('data-test="sheet-menu"', false);
});
