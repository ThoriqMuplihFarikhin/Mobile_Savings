<?php

use Illuminate\Support\Facades\Route;

it('mengumpulkan semua nama route literal yang dipakai blade', function () {
    $terdaftar = [];
    foreach (Route::getRoutes() as $route) {
        if ($route->getName() !== null) {
            $terdaftar[] = $route->getName();
        }
    }

    $dipakai = [];
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views')));
    foreach ($files as $file) {
        if ($file->isDir() || ! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }
        $isi = (string) file_get_contents($file->getPathname());
        preg_match_all("/route\(\s*['\"]([^'\"]+)['\"]/", $isi, $m);
        foreach ($m[1] as $nama) {
            $rel = str_replace(str_replace('\\', '/', resource_path()).'/', '', str_replace('\\', '/', $file->getPathname()));
            $dipakai[$nama][] = $rel;
        }
    }

    expect($dipakai)->not->toBeEmpty();

    $hilang = array_values(array_filter(
        array_keys($dipakai),
        fn (string $nama) => ! in_array($nama, $terdaftar, true),
    ));

    expect($hilang)->toBeEmpty(
        'Nama route dipakai di blade tetapi tidak ada di route list: '
        .collect($hilang)
            ->map(fn (string $n) => $n.' ('.implode(', ', array_unique($dipakai[$n])).')')
            ->implode('; '),
    );
});
