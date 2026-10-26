<?php

use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

function buatProdukLanding(array $data): ProdukTabungan
{
    return ProdukTabungan::create(array_merge([
        'persen_komisi' => 0,
        'status' => 'aktif',
    ], $data));
}

it('menampilkan produk aktif dan menyembunyikan produk nonaktif untuk tamu', function () {
    buatProdukLanding(['nama' => 'Tabungan Melimpah', 'tipe' => 'bebas', 'minimal_setor' => 5000]);
    buatProdukLanding(['nama' => 'Paket Rahasia Nonaktif', 'tipe' => 'paket', 'harga_per_hari' => 10000, 'status' => 'nonaktif']);

    $this->get('/')
        ->assertOk()
        ->assertSee('Tabungan Melimpah')
        ->assertDontSee('Paket Rahasia Nonaktif');
});

it('halaman landing tidak mengirim data statistik bisnis ke view', function () {
    User::factory()->nasabah()->count(5)->create();

    $this->get('/')
        ->assertOk()
        ->assertViewMissing('stats');
});

it('query landing minimal saat cache dingin dan nol saat cache hangat', function () {
    config(['cache.default' => 'array']);
    Cache::store('array')->flush();

    buatProdukLanding(['nama' => 'Tabungan Hemat', 'tipe' => 'bebas', 'minimal_setor' => 5000]);

    DB::enableQueryLog();
    $this->get('/')->assertOk();
    $dingin = count(DB::getQueryLog());
    DB::flushQueryLog();

    $this->get('/')->assertOk();
    $hangat = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($dingin)->toBeLessThanOrEqual(2)
        ->and($hangat)->toBe(0);
});

it('landing tetap tampil pada request kedua saat cache memakai store database', function () {
    config(['cache.default' => 'database']);
    buatProdukLanding(['nama' => 'Tabungan Cadangan', 'tipe' => 'bebas', 'minimal_setor' => 5000]);

    $this->get('/')->assertOk();

    $this->get('/')
        ->assertOk()
        ->assertSee('Tabungan Cadangan');
});

it('baris cache landing hanya memuat array publik: tanpa objek dan tanpa kata harga (D29)', function () {
    config(['cache.default' => 'database']);
    buatProdukLanding(['nama' => 'Tabungan Cadangan', 'tipe' => 'bebas', 'minimal_setor' => 5000]);

    $this->get('/')->assertOk();

    $baris = DB::table('cache')->where('key', 'like', '%landing:produk%')->first();
    $payload = (string) ($baris->value ?? '');

    expect($baris)->not->toBeNull()
        ->and($payload)->not->toContain('"harga"')
        ->and($payload)->not->toMatch('/(?:^|[;{])O:\d+:"/');
});

it('serializable_classes kembali false tanpa allowlist objek (D29)', function () {
    expect(config('cache.serializable_classes'))->toBeFalse();
});

it('pengguna login tetap dapat membuka halaman landing', function () {
    $user = User::factory()->kolektor()->create();

    $this->actingAs($user)
        ->get('/')
        ->assertOk();
});

it('tidak ada literal status berhasil untuk transaksi setoran di kode aplikasi', function () {
    $berkas = array_merge(
        collect(File::allFiles(app_path()))->map->getPathname()->all(),
        collect(File::allFiles(base_path('routes')))->map->getPathname()->all(),
    );

    $pelanggaran = array_filter($berkas, function (string $path) {
        $isi = file_get_contents($path);

        return (bool) preg_match('/status[\'"]\s*(?:,|=>)\s*[\'"]berhasil[\'"]/', $isi);
    });

    expect($pelanggaran)->toBe([]);
});
