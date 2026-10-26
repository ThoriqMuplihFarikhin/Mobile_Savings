<?php

use App\Livewire\Admin\SerahTerimaPaket;
use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * @return array{0: User, 1: KepesertaanPaket}
 */
function p61Fixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket P61',
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
        'metode_pengambilan' => $opsi['metode'] ?? null,
        'status_serah_terima' => 'belum',
        'komitmen_disetujui_pada' => now(),
        'komitmen_via' => 'migrasi',
    ]);

    if ($opsi['lunas'] ?? false) {
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

function p61JadikanBinaan(User $kolektor, User $nasabah): void
{
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);
}

function p61IsiFormKonfirmasi(Testable $tes, string $nama): Testable
{
    return $tes
        ->set('diterimaOleh', $nama)
        ->set('tanggalSerahTerima', now()->toDateString())
        ->set('buktiFoto', UploadedFile::fake()->image('bukti.jpg'));
}

it('nasabah memilih metode pengambilan setelah tanggal boleh cair dan tanpa tunggakan', function () {
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true]);

    Livewire::actingAs($nasabah)
        ->test(ProgresPaket::class)
        ->call('pilihMetodePengambilan', $kepesertaan->id, 'diantar_kolektor');

    expect($kepesertaan->fresh()->metode_pengambilan)->toBe('diantar_kolektor');
});

it('nasabah menolak memilih metode sebelum tanggal boleh cair', function () {
    [$nasabah, $kepesertaan] = p61Fixture([
        'lunas' => true,
        'tanggal_boleh_cair' => now()->addDay()->toDateString(),
    ]);

    Livewire::actingAs($nasabah)
        ->test(ProgresPaket::class)
        ->call('pilihMetodePengambilan', $kepesertaan->id, 'ambil_sendiri')
        ->assertSee('belum boleh cair');

    expect($kepesertaan->fresh()->metode_pengambilan)->toBeNull();
});

it('nasabah menolak memilih metode saat masih ada tunggakan', function () {
    [$nasabah, $kepesertaan] = p61Fixture();

    Livewire::actingAs($nasabah)
        ->test(ProgresPaket::class)
        ->call('pilihMetodePengambilan', $kepesertaan->id, 'ambil_sendiri')
        ->assertSee('Lunasi tunggakan');

    expect($kepesertaan->fresh()->metode_pengambilan)->toBeNull();
});

it('admin mengonfirmasi serah terima lengkap dengan foto bukti', function () {
    Queue::fake();
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    p61IsiFormKonfirmasi(
        Livewire::actingAs($admin)->test(SerahTerimaPaket::class),
        'Budi Santoso'
    )->call('konfirmasi', $kepesertaan->id);

    $kepesertaan->refresh();

    expect($kepesertaan->status_serah_terima)->toBe('sudah_diterima')
        ->and($kepesertaan->diterima_oleh)->toBe('Budi Santoso')
        ->and($kepesertaan->tanggal_serah_terima?->toDateString())->toBe(now()->toDateString())
        ->and(Storage::disk('local')->exists((string) $kepesertaan->bukti_foto_url))->toBeTrue()
        ->and(LogAktivitas::where('aksi', 'serah_terima_paket')->where('entitas_id', $kepesertaan->id)->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $nasabah->id)->where('judul', 'Serah Terima Paket')->exists())->toBeTrue();
});

it('menolak konfirmasi kedua kali dan tidak mencatat log ganda', function () {
    Queue::fake();
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    p61IsiFormKonfirmasi(
        Livewire::actingAs($admin)->test(SerahTerimaPaket::class),
        'Budi Santoso'
    )->call('konfirmasi', $kepesertaan->id);

    p61IsiFormKonfirmasi(
        Livewire::actingAs($admin)->test(SerahTerimaPaket::class),
        'Budi Santoso'
    )->call('konfirmasi', $kepesertaan->id)
        ->assertSee('sudah pernah diserahkan');

    expect(LogAktivitas::where('aksi', 'serah_terima_paket')->where('entitas_id', $kepesertaan->id)->count())->toBe(1);
});

it('kolektor penanggung jawab mengonfirmasi penyerahan diantar kolektor', function () {
    Queue::fake();
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'diantar_kolektor']);
    $kolektor = User::factory()->kolektor()->create();
    p61JadikanBinaan($kolektor, $nasabah);

    p61IsiFormKonfirmasi(
        Livewire::actingAs($kolektor)->test(SerahTerimaPaket::class),
        'Kolektor Kurir'
    )->call('konfirmasi', $kepesertaan->id);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('sudah_diterima');
});

it('kolektor tidak dapat mengonfirmasi metode ambil sendiri', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'ambil_sendiri']);
    $kolektor = User::factory()->kolektor()->create();
    p61JadikanBinaan($kolektor, $nasabah);

    p61IsiFormKonfirmasi(
        Livewire::actingAs($kolektor)->test(SerahTerimaPaket::class),
        'Kolektor Kurir'
    )->call('konfirmasi', $kepesertaan->id)
        ->assertSee('hanya dikonfirmasi admin');

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('kolektor tidak dapat mengonfirmasi nasabah di luar binaannya', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'diantar_kolektor']);
    $kolektor = User::factory()->kolektor()->create();

    p61IsiFormKonfirmasi(
        Livewire::actingAs($kolektor)->test(SerahTerimaPaket::class),
        'Kolektor Kurir'
    )->call('konfirmasi', $kepesertaan->id)
        ->assertSee('penanggung jawab');

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('menolak foto bukti yang bukan gambar', function () {
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SerahTerimaPaket::class)
        ->set('diterimaOleh', 'Budi Santoso')
        ->set('tanggalSerahTerima', now()->toDateString())
        ->set('buktiFoto', UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertHasErrors(['buktiFoto']);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('halaman serah terima hanya untuk admin dan kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($admin)->get('/serah-terima-paket')->assertOk();
    $this->actingAs($kolektor)->get('/serah-terima-paket')->assertOk();
    $this->actingAs($nasabah)->get('/serah-terima-paket')->assertForbidden();
});

it('menyajikan foto bukti hanya kepada pihak berwenang', function () {
    Storage::fake('local');
    [$nasabah, $kepesertaan] = p61Fixture(['lunas' => true, 'metode' => 'ambil_sendiri']);
    $admin = User::factory()->admin()->create();
    $nasabahLain = User::factory()->nasabah()->create();

    $path = 'serah-terima/bukti-p61.jpg';
    Storage::disk('local')->put($path, 'isi-gambar');
    $kepesertaan->update([
        'status_serah_terima' => 'sudah_diterima',
        'diterima_oleh' => 'Budi Santoso',
        'tanggal_serah_terima' => now()->toDateString(),
        'bukti_foto_url' => $path,
    ]);

    $this->actingAs($admin)->get(route('serah-terima.bukti', $kepesertaan))->assertOk();
    $this->actingAs($nasabah)->get(route('serah-terima.bukti', $kepesertaan))->assertOk();
    $this->actingAs($nasabahLain)->get(route('serah-terima.bukti', $kepesertaan))->assertForbidden();
});

it('kepesertaan yang sudah diterima keluar dari hitungan tunggakan dan daftar aktif', function () {
    [$nasabah, $kepesertaan] = p61Fixture();

    $kepesertaan->update(['status_serah_terima' => 'sudah_diterima']);

    expect($kepesertaan->hitungUlangKepesertaan(false))
        ->toBe(['tunggakan_hari' => 0, 'tunggakan_rupiah' => 0]);

    $this->actingAs($nasabah)->get('/dashboard')->assertOk()
        ->assertViewHas('kepesertaanAktif', fn ($aktif) => $aktif === null);
});
