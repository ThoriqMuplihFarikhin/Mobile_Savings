<?php

use App\Livewire\Admin\SerahTerimaPaket;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * Kepesertaan lunas + komitmen + metode, siap melewati gerbang D17.
 *
 * @return array{0: User, 1: KepesertaanPaket}
 */
function p95Fixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket 95',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'tanggal_boleh_cair' => $opsi['tanggal_boleh_cair'] ?? now()->toDateString(),
        'status' => 'aktif',
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'metode_pengambilan' => $opsi['metode'] ?? 'diantar_kolektor',
        'status_serah_terima' => $opsi['status_serah'] ?? 'belum',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);

    if (($opsi['status_serah'] ?? 'belum') === 'belum') {
        DB::table('transaksi_setoran')->insert([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produk->id,
            'kepesertaan_id' => $kepesertaan->id,
            'nominal' => 10000,
            'tanggal_transaksi' => now()->toDateString(),
            'tanggal_input_sistem' => now(),
            'input_by' => $nasabah->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return [$nasabah, $kepesertaan->fresh()];
}

/**
 * Kolektor baru dengan $nasabah sebagai binaan aktif.
 */
function p95BuatKolektor(User $nasabah): User
{
    $kolektor = User::factory()->kolektor()->create();

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return $kolektor;
}

/**
 * PNG acak sungguhan agar ukuran terkendali: ~3,6 MB (sisi 1100)
 * atau >5 MB (sisi 1500) karena data acak tak terkompresi zlib.
 */
function p95PngAcak(int $sisi): File
{
    $gambar = imagecreatetruecolor($sisi, $sisi);

    for ($y = 0; $y < $sisi; $y++) {
        for ($x = 0; $x < $sisi; $x++) {
            imagesetpixel($gambar, $x, $y, mt_rand(0, 0xFFFFFF));
        }
    }

    ob_start();
    imagepng($gambar);
    $isi = (string) ob_get_clean();
    imagedestroy($gambar);

    return UploadedFile::fake()->createWithContent('bukti.png', $isi);
}

function p95IsiForm(Testable $tes, ?File $foto = null): Testable
{
    $tes = $tes
        ->set('diterimaOleh', 'Petugas 95')
        ->set('tanggalSerahTerima', now()->toDateString());

    if ($foto !== null) {
        $tes = $tes->set('buktiFoto', $foto);
    }

    return $tes;
}

it('mewajibkan kolektor mengunggah foto bukti saat konfirmasi', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p95Fixture();
    $kolektor = p95BuatKolektor($nasabah);

    p95IsiForm(Livewire::actingAs($kolektor)->test(SerahTerimaPaket::class))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasErrors(['buktiFoto']);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('admin tidak dapat mengonfirmasi serah terima tanpa foto', function () {
    Storage::fake('local');
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    p95IsiForm(Livewire::actingAs($admin)->test(SerahTerimaPaket::class))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasErrors(['buktiFoto']);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('daftar serah terima kolektor hanya memuat nasabah binaannya', function () {
    [$binaan] = p95Fixture();
    [$bukanBinaan] = p95Fixture();
    $kolektor = p95BuatKolektor($binaan);

    Livewire::actingAs($kolektor)
        ->test(SerahTerimaPaket::class)
        ->assertSee($binaan->name)
        ->assertDontSee($bukanBinaan->name);
});

it('foto bukti terbuka bagi kolektor binaan dan tertutup bagi kolektor lain', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p95Fixture(['status_serah' => 'sudah_diterima']);
    $kolektorBinaan = p95BuatKolektor($nasabah);
    $kolektorLain = User::factory()->kolektor()->create();

    $path = 'serah-terima/bukti-95.jpg';
    Storage::disk('local')->put($path, 'isi-gambar');
    $kepesertaan->update(['bukti_foto_url' => $path]);

    $this->actingAs($kolektorBinaan)->get(route('serah-terima.bukti', $kepesertaan))->assertOk();
    $this->actingAs($kolektorLain)->get(route('serah-terima.bukti', $kepesertaan))->assertForbidden();
});

it('mencatat log aktivitas dengan path foto metode dan penerima', function () {
    Queue::fake();
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p95Fixture();
    $kolektor = p95BuatKolektor($nasabah);

    p95IsiForm(
        Livewire::actingAs($kolektor)->test(SerahTerimaPaket::class),
        UploadedFile::fake()->image('bukti.jpg')
    )->call('konfirmasi', $kepesertaan->id);

    $kepesertaan->refresh();
    $log = LogAktivitas::where('aksi', 'serah_terima_paket')
        ->where('entitas_id', $kepesertaan->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->detail['bukti_foto'])->toBe($kepesertaan->bukti_foto_url)
        ->and($log->detail['metode_pengambilan'])->toBe('diantar_kolektor')
        ->and($log->detail['diterima_oleh'])->toBe('Petugas 95')
        ->and($log->detail['tanggal_serah_terima'])->toBe(now()->toDateString());
});

it('menerima foto png berukuran lebih dari 2 MB', function () {
    Queue::fake();
    Storage::fake('local');
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    $foto = p95PngAcak(1100);
    expect($foto->getSize())->toBeGreaterThan(2048 * 1024)
        ->and($foto->getSize())->toBeLessThan(5120 * 1024);

    p95IsiForm(Livewire::actingAs($admin)->test(SerahTerimaPaket::class), $foto)
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasNoErrors();

    $kepesertaan->refresh();
    expect($kepesertaan->status_serah_terima)->toBe('sudah_diterima')
        ->and(Storage::disk('local')->exists((string) $kepesertaan->bukti_foto_url))->toBeTrue();
});

it('menolak foto png berukuran lebih dari 5 MB', function () {
    Storage::fake('local');
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    $foto = p95PngAcak(1500);
    expect($foto->getSize())->toBeGreaterThan(5120 * 1024);

    p95IsiForm(Livewire::actingAs($admin)->test(SerahTerimaPaket::class), $foto)
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasErrors(['buktiFoto']);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('menolak gambar di luar jpg jpeg png webp', function () {
    Storage::fake('local');
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    p95IsiForm(Livewire::actingAs($admin)->test(SerahTerimaPaket::class), UploadedFile::fake()->image('bukti.gif'))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasErrors(['buktiFoto']);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('menerima berkas webp untuk foto bukti', function () {
    Queue::fake();
    Storage::fake('local');
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    p95IsiForm(Livewire::actingAs($admin)->test(SerahTerimaPaket::class), UploadedFile::fake()->image('bukti.webp'))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasNoErrors();

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('sudah_diterima');
});

it('input foto membatasi tipe dan mencantumkan kompresi klien', function () {
    [, $kepesertaan] = p95Fixture(['metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SerahTerimaPaket::class)
        ->call('bukaKonfirmasi', $kepesertaan->id)
        ->assertSee('maks 5 MB')
        ->assertSee('accept="image/*" capture', false)
        ->assertSee('kompresFoto', false);
});

it('menampilkan tautan foto bukti kepada nasabah pemilik di progres paket', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p95Fixture(['status_serah' => 'sudah_diterima']);

    $path = 'serah-terima/bukti-95.jpg';
    Storage::disk('local')->put($path, 'isi-gambar');
    $kepesertaan->update(['bukti_foto_url' => $path]);

    $this->actingAs($nasabah)
        ->get(route('nasabah.progres-paket.index'))
        ->assertOk()
        ->assertSee(route('serah-terima.bukti', $kepesertaan));
});

it('menampilkan tautan foto bukti kepada admin di detail nasabah', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p95Fixture(['status_serah' => 'sudah_diterima']);
    $admin = User::factory()->admin()->create();

    $path = 'serah-terima/bukti-95.jpg';
    Storage::disk('local')->put($path, 'isi-gambar');
    $kepesertaan->update(['bukti_foto_url' => $path]);

    $this->actingAs($admin)
        ->get(route('admin.nasabah.detail', $nasabah))
        ->assertOk()
        ->assertSee(route('serah-terima.bukti', $kepesertaan));
});
