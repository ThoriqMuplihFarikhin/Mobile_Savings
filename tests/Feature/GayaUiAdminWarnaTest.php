<?php

use App\Models\User;

/**
 * Fase 4 migrasi DESIGN.md: penyelarasan warna layar admin. Aksi memakai warna
 * aksen portal (DESIGN §11), status tetap dari tabel pemetaan §2, dan palet lama
 * (indigo, amber, emerald, rose, navy, gold, slate) tidak dipakai lagi. K2: abu
 * kebiruan `gray-*` (#111827/#6b7280 — "navy tint" admin) diganti token netral
 * DESIGN (ink/body/mute/subtle, hairline, inset) di seluruh view admin.
 *
 * Pemeriksaan warna dilakukan terhadap berkas view admin, bukan HTML hasil render,
 * karena komponen Flux di vendor memakai `text-amber-`/`text-rose-` bawaannya.
 *
 * @return array<int, string>
 */
function paletLamaLayarAdmin(): array
{
    return [
        'bg-indigo-',
        'text-indigo-',
        'bg-amber-',
        'text-amber-',
        'bg-emerald-',
        'text-emerald-',
        'bg-rose-',
        'text-rose-',
        'bg-purple-',
        'text-purple-',
        'bg-violet-',
        'text-violet-',
        'bg-teal-',
        'text-teal-',
        'bg-navy-',
        'text-navy-',
        'text-gold',
        'dark:text-slate-',
        'dark:bg-slate-',
        '-gray-',
        'focus:border-blue-',
        'bg-gradient',
        'backdrop-blur',
    ];
}

/**
 * @return array<int, string>
 */
function berkasViewAdmin(): array
{
    $scope = [
        'resources/views/livewire/admin',
        'resources/views/pages/admin',
        'resources/views/components/admin',
        'resources/views/layouts/app',
        'resources/views/layouts/admin.blade.php',
        'resources/views/components/kpi-card.blade.php',
    ];

    $berkas = [];

    foreach ($scope as $path) {
        $absolut = base_path($path);

        if (is_file($absolut)) {
            $berkas[] = $absolut;

            continue;
        }

        if (! is_dir($absolut)) {
            continue;
        }

        $pendulang = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($absolut, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($pendulang as $item) {
            if ($item->isFile() && str_ends_with($item->getFilename(), '.blade.php')) {
                $berkas[] = $item->getPathname();
            }
        }
    }

    return array_values(array_unique($berkas));
}

/**
 * @return array<int, string>
 */
function paletLamaPadaBerkasAdmin(): array
{
    $pelanggaran = [];

    foreach (berkasViewAdmin() as $berkas) {
        $isi = file_get_contents($berkas);

        foreach (paletLamaLayarAdmin() as $larangan) {
            if (str_contains($isi, $larangan)) {
                $pelanggaran[] = str_replace(base_path(), '', $berkas).' => '.$larangan;
            }
        }
    }

    return $pelanggaran;
}

it('view admin tidak memakai palet warna lama, gradien, atau blur', function () {
    expect(paletLamaPadaBerkasAdmin())->toBe([]);
});

it('dashboard admin memakai aksen portal sebagai warna aksi', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('data-portal="admin"')
        ->toContain('bg-accent');
});

it('layar keuangan admin memakai aksen portal, bukan hijau nasabah', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)
        ->get(route('admin.kas-kolektor.index'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('data-portal="admin"')
        ->toContain('bg-accent');
});

it('halaman edit profil admin memakai token avatars, bukan zinc', function () {
    $admin = User::factory()->admin()->create();

    $html = $this->actingAs($admin)
        ->get(route('admin.settings.profile'))
        ->assertOk()
        ->getContent();

    $sumber = file_get_contents(base_path('resources/views/livewire/admin/settings/profile.blade.php'));

    expect($html)
        ->toContain('bg-accent text-accent-foreground', false)
        ->and($sumber)
        ->not->toContain('bg-zinc-')
        ->not->toContain('text-white');
});
