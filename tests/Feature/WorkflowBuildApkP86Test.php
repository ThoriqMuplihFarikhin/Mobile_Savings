<?php

it('workflow build-apk menjalankan matrix tiga flavor dengan java 17 dan secret keystore', function () {
    $path = base_path('.github/workflows/build-apk.yml');

    expect($path)->toBeFile();

    $workflow = (string) file_get_contents($path);

    expect($workflow)
        ->toContain('workflow_dispatch')
        ->toContain('apk-*')
        ->toContain('matrix')
        ->toContain('nasabah')
        ->toContain('kolektor')
        ->toContain('admin')
        ->toContain('java-version: "17"')
        ->toContain('setup-node')
        ->toContain('secrets.ANDROID_KEYSTORE_BASE64')
        ->toContain('vars.APP_DOMAIN')
        ->toContain('github.run_number')
        ->toContain('upload-artifact')
        ->toContain('build-flavor.sh')
        ->toContain('fromJson');
});

it('gitignore menutup artefak android dan keystore dari kontrol versi', function () {
    $gitignore = (string) file_get_contents(base_path('.gitignore'));

    foreach (['*.jks', '*.keystore', 'mobile/build/', 'mobile/node_modules/'] as $baris) {
        expect($gitignore)->toContain($baris);
    }
});
