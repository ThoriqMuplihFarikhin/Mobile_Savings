<?php

it('flavors.json memuat tiga varian aplikasi dengan id dan start path benar', function () {
    $path = base_path('mobile/flavors.json');

    expect($path)->toBeFile();

    $flavors = json_decode((string) file_get_contents($path), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and($flavors)->toHaveKeys(['nasabah', 'kolektor', 'admin'])
        ->and($flavors['nasabah']['applicationId'])->toBe('id.tabungan.nasabah')
        ->and($flavors['nasabah']['name'])->toBe('Tabungan')
        ->and($flavors['nasabah']['startPath'])->toBe('/login')
        ->and($flavors['kolektor']['applicationId'])->toBe('id.tabungan.kolektor')
        ->and($flavors['kolektor']['name'])->toBe('Tabungan Kolektor')
        ->and($flavors['kolektor']['startPath'])->toBe('/login/kolektor')
        ->and($flavors['admin']['applicationId'])->toBe('id.tabungan.admin')
        ->and($flavors['admin']['name'])->toBe('Tabungan Admin')
        ->and($flavors['admin']['startPath'])->toBe('/login/admin');
});

it('template konfigurasi capacitor menuntut https, allow navigation domain, dan error path offline', function () {
    $path = base_path('mobile/template/capacitor.config.json');

    expect($path)->toBeFile();

    $config = json_decode((string) file_get_contents($path), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and($config['appId'])->toBe('{{APPLICATION_ID}}')
        ->and($config['appName'])->toBe('{{APP_NAME}}')
        ->and($config['server']['url'])->toBe('https://{{APP_DOMAIN}}{{START_PATH}}')
        ->and($config['server']['cleartext'])->toBeFalse()
        ->and($config['server']['errorPath'])->toBe('/offline')
        ->and($config['server']['allowNavigation'])->toContain('{{APP_DOMAIN}}')
        ->and($config['server']['android']['appendUserAgent'])->toBe('TabunganApp/{{FLAVOR}}');
});

it('package.json mobile memasang capacitor inti, android, assets, dan app', function () {
    $path = base_path('mobile/package.json');

    expect($path)->toBeFile();

    $package = json_decode((string) file_get_contents($path), true);

    expect(json_last_error())->toBe(JSON_ERROR_NONE)
        ->and($package['dependencies'])->toHaveKeys(['@capacitor/core', '@capacitor/android', '@capacitor/app'])
        ->and($package['devDependencies'])->toHaveKeys(['@capacitor/cli', '@capacitor/assets']);
});

it('skrip build-flavor menyiapkan android, izin, ikon, penandatanganan, dan debug fallback', function () {
    $path = base_path('mobile/scripts/build-flavor.sh');

    expect($path)->toBeFile();

    $skrip = (string) file_get_contents($path);

    expect($skrip)
        ->toContain('npx cap add android')
        ->toContain('android.permission.INTERNET')
        ->toContain('android.permission.CAMERA')
        ->toContain('android.permission.ACCESS_FINE_LOCATION')
        ->toContain('android.permission.ACCESS_COARSE_LOCATION')
        ->toContain('capacitor-assets generate')
        ->toContain('capacitor_label')
        ->toContain('versionCode')
        ->toContain('ANDROID_KEYSTORE_BASE64')
        ->toContain('apksigner')
        ->toContain('assembleRelease')
        ->toContain('assembleDebug');
});

it('aset ikon dan splash ketiga flavor tersedia sebagai png', function () {
    foreach (['nasabah', 'kolektor', 'admin'] as $flavor) {
        foreach (['icon.png', 'splash.png'] as $aset) {
            $path = base_path("mobile/assets/{$flavor}/{$aset}");

            expect($path)->toBeFile();

            $info = getimagesize($path);

            expect($info)->not->toBeFalse()
                ->and($info['mime'])->toBe('image/png')
                ->and($info[0])->toBe(1024)
                ->and($info[1])->toBe(1024);
        }
    }
});

it('head memuat listener tombol back kapasitor untuk android', function () {
    $head = (string) file_get_contents(resource_path('views/partials/head.blade.php'));

    expect($head)
        ->toContain('backButton')
        ->toContain('exitApp');
});
