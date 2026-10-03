<?php

use Illuminate\Support\Str;

/**
 * Tes penjaga P1.1: proxy $wire Livewire mendahulukan properti publik sebelum
 * fallback ke method. Nama yang sama sebagai properti publik + method publik
 * membuat wire:click="nama(...)" gagal diam-diam (TypeError di browser),
 * sementara Livewire::test()->call() tetap memanggil method PHP — jadi tes
 * fitur biasa tidak pernah menangkapnya.
 */
function lc_files(string $dir): array
{
    if (! is_dir($dir)) {
        return [];
    }

    $hasil = [];
    $iter = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iter as $berkas) {
        if ($berkas->isFile()) {
            $hasil[] = $berkas->getPathname();
        }
    }

    sort($hasil);

    return $hasil;
}

function lc_ekstrakAksiWire(string $konten): array
{
    preg_match_all(
        '/wire:(?:click|submit|change)(?:\.[\w.\-]+)?\s*=\s*(["\'])(.*?)\1/s',
        $konten,
        $cocok,
        PREG_SET_ORDER
    );

    $nama = [];

    foreach ($cocok as $m) {
        $nilai = trim($m[2]);

        if ($nilai === '' || str_starts_with($nilai, '$')) {
            continue;
        }

        if (preg_match('/^([A-Za-z_]\w*)\s*(\(|$)/', $nilai, $kepala)) {
            $nama[] = $kepala[1];
        }
    }

    return array_values(array_unique($nama));
}

function lc_klasDariViewLivewire(string $path): ?string
{
    $rel = str_replace('\\', '/', $path);
    $rel = substr($rel, strpos($rel, '/resources/views/livewire/') + 1);
    $segments = explode('/', substr($rel, strlen('resources/views/livewire/')));
    $segments[count($segments) - 1] = preg_replace('/\.blade\.php$/', '', $segments[count($segments) - 1]);
    $kelas = 'App\\Livewire\\'.implode('\\', array_map(Str::studly(...), $segments));

    return class_exists($kelas) ? $kelas : null;
}

test('tidak ada nama yang menjadi properti publik sekaligus method publik di app/Livewire', function () {
    $bentrok = [];

    foreach (lc_files(dirname(__DIR__, 2).'/app/Livewire') as $berkas) {
        $rel = str_replace('\\', '/', substr($berkas, strlen(dirname(__DIR__, 2)) + 1));
        $kelas = 'App\\'.str_replace('/', '\\', preg_replace('/\.php$/', '', substr($rel, strlen('app/'))));

        if (! class_exists($kelas) && ! trait_exists($kelas)) {
            continue;
        }

        $refleksi = new ReflectionClass($kelas);

        $properti = array_map(
            fn (ReflectionProperty $p) => $p->getName(),
            $refleksi->getProperties(ReflectionProperty::IS_PUBLIC)
        );

        $method = array_map(
            fn (ReflectionMethod $m) => $m->getName(),
            $refleksi->getMethods(ReflectionMethod::IS_PUBLIC)
        );

        foreach (array_intersect($properti, $method) as $nama) {
            $bentrok[] = "{$kelas}: \${$nama}";
        }
    }

    expect($bentrok)->toBeEmpty(
        'Nama berikut menjadi properti publik sekaligus method publik — wire:click akan gagal di browser: '
        .implode(', ', $bentrok)
    );
});

test('setiap aksi wire di view resources/views/livewire adalah method publik dan bukan properti publik komponennya', function () {
    $viols = [];

    foreach (lc_files(dirname(__DIR__, 2).'/resources/views/livewire') as $berkas) {
        $aksi = lc_ekstrakAksiWire((string) file_get_contents($berkas));

        if ($aksi === []) {
            continue;
        }

        $view = substr(str_replace('\\', '/', $berkas), strpos(str_replace('\\', '/', $berkas), '/resources/views/') + 1);
        $kelas = lc_klasDariViewLivewire($berkas);

        if ($kelas === null) {
            $viols[] = "{$view}: view tanpa kelas komponen memuat aksi wire (".implode(', ', $aksi).')';

            continue;
        }

        $refleksi = new ReflectionClass($kelas);

        foreach ($aksi as $nama) {
            if (! $refleksi->hasMethod($nama) || ! $refleksi->getMethod($nama)->isPublic()) {
                $viols[] = "{$view}: wire:{$nama} → method publik tidak ditemukan di {$kelas}";
            }

            if ($refleksi->hasProperty($nama) && $refleksi->getProperty($nama)->isPublic()) {
                $viols[] = "{$view}: wire:{$nama} → \${$nama} adalah properti publik di {$kelas} (proxy \$wire memanggil properti, bukan method)";
            }
        }
    }

    expect($viols)->toBeEmpty('Aksi wire rusak/tidak terverifikasi: '.implode(' | ', $viols));
});

test('setiap aksi wire di halaman pages adalah method publik dan bukan properti publik komponennya', function () {
    $viols = [];

    foreach (lc_files(dirname(__DIR__, 2).'/resources/views/pages') as $berkas) {
        $konten = (string) file_get_contents($berkas);
        $aksi = lc_ekstrakAksiWire($konten);

        if ($aksi === []) {
            continue;
        }

        $rel = str_replace('\\', '/', substr($berkas, strlen(dirname(__DIR__, 2)) + 1));
        $header = strstr($konten, '?>', true) ?: $konten;
        $halamanKomponen = str_contains($header, 'class extends Component');

        if (! $halamanKomponen) {
            $viols[] = "{$rel}: halaman berisi aksi wire namun bukan komponen Livewire (".implode(', ', $aksi).')';

            continue;
        }

        preg_match_all('/function\s+(\w+)\s*\(/', $header, $mMethod);
        $method = $mMethod[1];

        preg_match_all('/public\s+(?:[A-Za-z_][\w\\\\]*(?:\|[\w\\\\]+)?\s+)?\$(\w+)/', $header, $mProp);
        $properti = $mProp[1];

        foreach ($aksi as $nama) {
            if (! in_array($nama, $method, true)) {
                $viols[] = "{$rel}: wire:{$nama} → method tidak ditemukan di halaman Volt";
            }

            if (in_array($nama, $properti, true)) {
                $viols[] = "{$rel}: wire:{$nama} → \${$nama} adalah properti publik halaman Volt";
            }
        }
    }

    expect($viols)->toBeEmpty('Aksi wire di halaman rusak/tidak terverifikasi: '.implode(' | ', $viols));
});
