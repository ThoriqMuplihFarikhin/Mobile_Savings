<?php

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

/**
 * F3 / D28 — pemeriksaan kesiapan produksi `deploy:check`.
 *
 * Seluruh butir dibaca dari `config()` (aman saat config di-cache) dan dari
 * `public_path()`, sehingga tiap butir bisa dijatuhkan satu per satu lewat
 * override config serta public path sementara.
 */

/**
 * Konfigurasi layak produksi: seluruh butir env lulus.
 */
function konfigurasiLayakProduksiD28(): void
{
    config([
        'app.env' => 'production',
        'app.debug' => false,
        'app.url' => 'https://tabungan.example.test',
        'session.secure' => true,
        'database.default' => 'mysql',
        'cache.default' => 'database',
        'queue.default' => 'database',
    ]);
}

/**
 * Jalankan aksi di atas public path sementara berisi artefak tertentu.
 *
 * @param  array<int, string>  $artefak  berkas relatif terhadap public yang dibuat
 */
function denganPublicSementaraD28(array $artefak, callable $aksi): void
{
    $dir = sys_get_temp_dir().'/deploy-check-d28-'.uniqid();
    mkdir($dir, 0777, true);

    foreach ($artefak as $relatif) {
        $path = $dir.'/'.$relatif;

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, '{}');
    }

    $asli = app()->publicPath();
    app()->usePublicPath($dir);

    try {
        $aksi();
    } finally {
        app()->usePublicPath($asli);
        hapusDirektoriD28($dir);
    }
}

function hapusDirektoriD28(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }

    foreach (array_diff((array) scandir($dir), ['.', '..']) as $entri) {
        $path = $dir.'/'.$entri;

        is_dir($path) ? hapusDirektoriD28($path) : unlink($path);
    }

    rmdir($dir);
}

it('deploy:check lulus dengan konfigurasi produksi layak (D28)', function () {
    konfigurasiLayakProduksiD28();

    denganPublicSementaraD28(['build/manifest.json'], function () {
        $this->artisan('deploy:check')
            ->expectsOutputToContain('[LULUS] APP_ENV=production')
            ->expectsOutputToContain('0 gagal')
            ->assertSuccessful();
    });
});

it('deploy:check gagal pada setiap butir yang rusak (D28)', function (array $skenario) {
    konfigurasiLayakProduksiD28();
    config($skenario['config'] ?? []);

    $storageAsli = app()->storagePath();

    if (($skenario['penjadwal'] ?? true) === false) {
        Artisan::call('list');
        $this->app->instance(Schedule::class, new Schedule);
    }

    if (($skenario['storage'] ?? true) === false) {
        app()->useStoragePath(sys_get_temp_dir().'/deploy-check-d28-storage-hilang');
    }

    try {
        denganPublicSementaraD28(
            $skenario['berkas'] ?? ['build/manifest.json'],
            function () use ($skenario) {
                $this->artisan('deploy:check')
                    ->expectsOutputToContain($skenario['pesan'])
                    ->assertFailed();
            }
        );
    } finally {
        config(['database.default' => 'mysql']);
        app()->useStoragePath($storageAsli);
    }
})->with([
    'APP_ENV bukan production' => [['config' => ['app.env' => 'testing'], 'pesan' => '[GAGAL] APP_ENV']],
    'APP_DEBUG true' => [['config' => ['app.debug' => true], 'pesan' => '[GAGAL] APP_DEBUG']],
    'APP_URL tanpa https' => [['config' => ['app.url' => 'http://tabungan.example.test'], 'pesan' => '[GAGAL] APP_URL']],
    'SESSION_SECURE_COOKIE false' => [['config' => ['session.secure' => false], 'pesan' => '[GAGAL] SESSION_SECURE_COOKIE']],
    'driver DB bukan mysql' => [['config' => ['database.default' => 'sqlite'], 'pesan' => '[GAGAL] DB_DRIVER']],
    'CACHE_STORE array' => [['config' => ['cache.default' => 'array'], 'pesan' => '[GAGAL] CACHE_STORE']],
    'QUEUE_CONNECTION sync' => [['config' => ['queue.default' => 'sync'], 'pesan' => '[GAGAL] QUEUE_CONNECTION']],
    'public/hot ada' => [['berkas' => ['build/manifest.json', 'hot'], 'pesan' => '[GAGAL] PUBLIC_HOT']],
    'fonts-manifest.dev.json ada' => [['berkas' => ['build/manifest.json', 'fonts-manifest.dev.json'], 'pesan' => '[GAGAL] FONTS_MANIFEST_DEV']],
    'public/build/manifest.json hilang' => [['berkas' => [], 'pesan' => '[GAGAL] BUILD_MANIFEST']],
    'APP_KEY kosong' => [['config' => ['app.key' => ''], 'pesan' => '[GAGAL] APP_KEY']],
    'penjadwal tidak terdaftar' => [['penjadwal' => false, 'pesan' => '[GAGAL] SCHEDULE']],
    'storage tidak dapat ditulis' => [['storage' => false, 'pesan' => '[GAGAL] STORAGE_WRITABLE']],
]);

it('setelan kas minus dan batas langsung dilaporkan sebagai info, bukan gagal (D28)', function () {
    konfigurasiLayakProduksiD28();

    denganPublicSementaraD28(['build/manifest.json'], function () {
        $this->artisan('deploy:check')
            ->expectsOutputToContain('[INFO] izinkan_kas_minus=')
            ->expectsOutputToContain('[INFO] offline_penarikan_batas_langsung=')
            ->assertSuccessful();
    });
});

it('.gitignore memuat artefak dev yang dilarang ada di produksi (D28)', function () {
    $gitignore = (string) file_get_contents(base_path('.gitignore'));

    expect($gitignore)
        ->toContain('/public/hot')
        ->toContain('/public/fonts-manifest.dev.json');
});

it('composer menyediakan skrip deploy:check (D28)', function () {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true);

    expect($composer['scripts']['deploy:check'] ?? null)
        ->toContain('@php artisan deploy:check');
});

it('README bagian deploy memuat composer deploy:check (D28)', function () {
    $readme = (string) file_get_contents(base_path('README.md'));

    expect($readme)->toContain('composer deploy:check');
});

it('workflow CI menjalankan deploy:check sebagai langkah dokumentasi yang tidak menjatuhkan CI (D28)', function () {
    $yml = (string) file_get_contents(base_path('.github/workflows/tests.yml'));

    expect($yml)
        ->toContain('deploy:check')
        ->toContain('continue-on-error: true');
});
