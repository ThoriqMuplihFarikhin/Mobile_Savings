<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * F4 / D29 — smoke test dengan store nyata (database).
 *
 * Seluruh tes inti memakai `array`; kelas bug "lolos di array, gagal di
 * produksi" (P9.7) hanya terbuka ketika nilai benar-benar di-serialize dan
 * di-unserialize antar request.
 */

/**
 * Peta halaman kunci per peran.
 *
 * @return array<string, array<int, string>>
 */
function halamanKunciD29(User $admin, User $kolektor, User $nasabah): array
{
    return [
        'tamu' => ['/', route('login'), route('login.kolektor'), route('login.admin')],
        'admin' => [route('dashboard'), route('admin.laporan.index'), route('admin.persetujuan.index')],
        'kolektor' => [route('dashboard')],
        'nasabah' => [route('dashboard')],
    ];
}

it('halaman kunci tetap hidup dua kali dengan cache dan queue store database (D29)', function () {
    config([
        'cache.default' => 'database',
        'queue.default' => 'database',
    ]);

    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $halaman = halamanKunciD29($admin, $kolektor, $nasabah);

    foreach ([1, 2] as $putaran) {
        $this->app['auth']->guard()->logout();

        foreach ($halaman['tamu'] as $uri) {
            $this->get($uri)->assertOk();
        }

        foreach (['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah] as $peran => $pengguna) {
            foreach ($halaman[$peran] as $uri) {
                $this->actingAs($pengguna)->get($uri)->assertOk();
            }
        }
    }

    $baris = DB::table('cache')->pluck('value');

    expect($baris)->not->toBeEmpty();

    foreach ($baris as $nilai) {
        expect((string) $nilai)->not->toMatch('/(?:^|[;{])O:\d+:"/');
    }
});
