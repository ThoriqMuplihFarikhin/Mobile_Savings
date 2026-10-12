<?php

use Illuminate\Support\Facades\File;

/**
 * F4 / D29 — penjaga cache: kode aplikasi hanya boleh menyimpan skalar/array.
 *
 * Store `database` meng-serialize nilai cache. Bila objek (mis. koleksi
 * Eloquent) ikut masuk, produksi dengan `serializable_classes = false`
 * akan gagal unserialize — persis bug P9.7 yang tertangkap telat.
 */

/**
 * Ekstrak sumber satu panggilan `Cache::<metode>(...)` mulai dari posisi `Cache::`.
 */
function ekstrakPanggilanCacheD29(string $isi, int $posisi): string
{
    $buka = strpos($isi, '(', $posisi);

    if ($buka === false) {
        return '';
    }

    $kedalaman = 0;
    $panjang = strlen($isi);

    for ($i = $buka; $i < $panjang; $i++) {
        if ($isi[$i] === '(') {
            $kedalaman++;

            continue;
        }

        if ($isi[$i] === ')') {
            $kedalaman--;

            if ($kedalaman === 0) {
                return substr($isi, $posisi, $i - $posisi + 1);
            }
        }
    }

    return '';
}

/**
 * Peta berkas relatif => sumber tiap panggilan penulisan `Cache::` di kode aplikasi.
 *
 * @return array<string, array<int, string>>
 */
function sensusPanggilanCacheD29(): array
{
    $berkas = array_merge(
        collect(File::allFiles(app_path()))->map->getPathname()->all(),
        collect(File::allFiles(base_path('routes')))->map->getPathname()->all(),
    );

    $hasil = [];

    foreach ($berkas as $path) {
        $isi = (string) file_get_contents($path);
        preg_match_all(
            '/Cache::(?:putMany|rememberForever|remember|forever|add|put)\s*\(/',
            $isi,
            $m,
            PREG_OFFSET_CAPTURE
        );

        if ($m[0] === []) {
            continue;
        }

        $rel = str_replace(str_replace('\\', '/', base_path()).'/', '', str_replace('\\', '/', $path));
        $hasil[$rel] = array_map(
            fn (array $cocok): string => ekstrakPanggilanCacheD29($isi, $cocok[1]),
            $m[0]
        );
    }

    ksort($hasil);

    return $hasil;
}

it('menutup rapat seluruh panggilan penulisan Cache:: di kode aplikasi (D29)', function () {
    $sensus = array_map('count', sensusPanggilanCacheD29());

    expect($sensus)->toBe([
        'app/Actions/Setoran/CatatSetoranAction.php' => 1,
        'app/Console/Commands/KirimPengingatTunggakan.php' => 1,
        'app/Http/Controllers/LandingController.php' => 1,
        'app/Models/AdminSetting.php' => 1,
        'app/Providers/FortifyServiceProvider.php' => 1,
    ]);
});

it('penulisan Cache:: tidak pernah menyimpan objek query builder (D29)', function () {
    $melanggar = [];

    foreach (sensusPanggilanCacheD29() as $berkas => $daftar) {
        foreach ($daftar as $sumber) {
            $adaHasilObjek = (bool) preg_match(
                '/(?:->|::)(?:get|first|find|findOrFail|paginate|cursor)\s*\(/',
                $sumber
            );
            $diubahArray = (bool) preg_match('/->(?:all|toArray)\s*\(/', $sumber);

            if ($adaHasilObjek && ! $diubahArray) {
                $melanggar[] = $berkas.': '.str_replace("\n", ' ', $sumber);
            }
        }
    }

    expect($melanggar)->toBe([]);
});
