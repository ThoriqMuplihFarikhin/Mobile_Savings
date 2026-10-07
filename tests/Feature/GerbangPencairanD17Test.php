<?php

use App\Actions\Penarikan\AjukanPenarikanAction;
use App\Livewire\Admin\SerahTerimaPaket;
use App\Livewire\Nasabah\ProgresPaket;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

/**
 * Fixture gerbang pencairan tunggal (D17).
 *
 * Opsi: komitmen (bool, default true), serah, metode, tanggal_boleh_cair,
 * toggle_target (bool), setoran (nominal lunas), tanggal_mulai,
 * periode_selesai.
 *
 * @return array{0: User, 1: ProdukTabungan, 2: KepesertaanPaket}
 */
function d17Fixture(array $opsi = []): array
{
    $nasabah = User::factory()->nasabah()->create();
    $tanggalMulai = $opsi['tanggal_mulai'] ?? now()->toDateString();

    $produk = ProdukTabungan::create([
        'nama' => 'Paket D17',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'periode_mulai' => $tanggalMulai,
        'periode_selesai' => $opsi['periode_selesai'] ?? now()->addDays(9)->toDateString(),
        'tanggal_boleh_cair' => array_key_exists('tanggal_boleh_cair', $opsi)
            ? $opsi['tanggal_boleh_cair']
            : now()->toDateString(),
        'boleh_cair_saat_target' => $opsi['toggle_target'] ?? false,
        'status' => 'aktif',
    ]);

    $komitmen = $opsi['komitmen'] ?? true;

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => $tanggalMulai,
        'status_serah_terima' => $opsi['serah'] ?? 'belum',
        'metode_pengambilan' => $opsi['metode'] ?? null,
        'komitmen_disetujui_pada' => $komitmen ? now() : null,
        'komitmen_via' => $komitmen ? 'mandiri' : null,
    ]);

    if (($opsi['setoran'] ?? 0) > 0) {
        DB::table('transaksi_setoran')->insert([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produk->id,
            'kepesertaan_id' => $kepesertaan->id,
            'nominal' => $opsi['setoran'],
            'tanggal_transaksi' => now()->toDateString(),
            'tanggal_input_sistem' => now(),
            'input_by' => $nasabah->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return [$nasabah, $produk, $kepesertaan->fresh()];
}

it('statusPencairan menolak sebelum komitmen dicatat', function () {
    [, , $kepesertaan] = d17Fixture(['komitmen' => false, 'setoran' => 10000]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_komitmen')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_BELUM_KOMITMEN);
});

it('statusPencairan menolak kepesertaan yang sudah diserahkan', function () {
    [, , $kepesertaan] = d17Fixture(['serah' => 'sudah_diterima', 'setoran' => 10000]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('sudah_diserahkan')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_SUDAH_DISERAHKAN);
});

it('statusPencairan menolak sebelum tanggal boleh cair', function () {
    [, , $kepesertaan] = d17Fixture([
        'setoran' => 10000,
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_waktunya')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_BELUM_WAKTU);
});

it('statusPencairan menolak saat tunggakan meski tanggal sudah lewat', function () {
    [, , $kepesertaan] = d17Fixture();

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('ada_tunggakan')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_TUNGGAKAN);
});

it('statusPencairan mengizinkan setelah tanggal dengan tunggakan nol', function () {
    [, , $kepesertaan] = d17Fixture(['setoran' => 10000]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeTrue()
        ->and($status['kode'])->toBe('ok')
        ->and($status['alasan'])->toBeNull();
});

it('statusPencairan menolak tanggal kosong dengan pesan khusus', function () {
    [, , $kepesertaan] = d17Fixture([
        'setoran' => 10000,
        'tanggal_boleh_cair' => null,
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_waktunya')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_TANGGAL_KOSONG);
});

it('statusPencairan mengizinkan target tercapai sebelum tanggal bila toggle aktif', function () {
    [, , $kepesertaan] = d17Fixture([
        'toggle_target' => true,
        'setoran' => 100000,
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeTrue()
        ->and($status['kode'])->toBe('ok')
        ->and($status['alasan'])->toBeNull();
});

it('statusPencairan tetap menolak target tercapai sebelum tanggal bila toggle mati', function () {
    [, , $kepesertaan] = d17Fixture([
        'toggle_target' => false,
        'setoran' => 100000,
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_waktunya')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_BELUM_WAKTU);
});

it('statusPencairan menolak shortcut target sebelum komitmen dicatat', function () {
    [, , $kepesertaan] = d17Fixture([
        'komitmen' => false,
        'toggle_target' => true,
        'setoran' => 100000,
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_komitmen')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_BELUM_KOMITMEN);
});

it('statusPencairan menolak toggle target saat target belum tercapai', function () {
    [, , $kepesertaan] = d17Fixture([
        'toggle_target' => true,
        'setoran' => 10000,
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    $status = $kepesertaan->statusPencairan();

    expect($status['boleh'])->toBeFalse()
        ->and($status['kode'])->toBe('belum_waktunya')
        ->and($status['alasan'])->toBe(KepesertaanPaket::ALASAN_BELUM_WAKTU);
});

it('AjukanPenarikanAction memakai pesan gerbang statusPencairan', function () {
    [$nasabah, $produk] = d17Fixture([
        'komitmen' => false,
        'tanggal_boleh_cair' => now()->subDay()->toDateString(),
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    expect(fn () => (new AjukanPenarikanAction)->execute($nasabah, $produk, 50000, 'online'))
        ->toThrow(Exception::class, KepesertaanPaket::ALASAN_BELUM_KOMITMEN);
});

it('pilihMetodePengambilan menampilkan alasan statusPencairan', function () {
    [$nasabah, , $kepesertaan] = d17Fixture(['komitmen' => false]);

    Livewire::actingAs($nasabah)
        ->test(ProgresPaket::class)
        ->call('pilihMetodePengambilan', $kepesertaan->id, 'ambil_sendiri')
        ->assertSee(KepesertaanPaket::ALASAN_BELUM_KOMITMEN);

    expect($kepesertaan->fresh()->metode_pengambilan)->toBeNull();
});

it('konfirmasi serah terima ditolak di dalam transaksi saat belum waktunya', function () {
    Storage::fake('local');
    [, , $kepesertaan] = d17Fixture([
        'setoran' => 10000,
        'metode' => 'ambil_sendiri',
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(SerahTerimaPaket::class)
        ->set('diterimaOleh', 'Budi Santoso')
        ->set('tanggalSerahTerima', now()->toDateString())
        ->set('buktiFoto', UploadedFile::fake()->image('bukti.jpg'))
        ->call('konfirmasi', $kepesertaan->id)
        ->assertSee(KepesertaanPaket::ALASAN_BELUM_WAKTU);

    expect($kepesertaan->fresh()->status_serah_terima)->toBe('belum');
});

it('daftar siap serah terima hanya menampilkan kepesertaan yang lulus gerbang', function () {
    $admin = User::factory()->admin()->create();

    d17Fixture([
        'setoran' => 10000,
        'metode' => 'ambil_sendiri',
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    Livewire::actingAs($admin)
        ->test(SerahTerimaPaket::class)
        ->assertViewHas('jumlahSiap', 0)
        ->assertViewHas('kepesertaan', fn ($daftar) => $daftar->isEmpty());

    d17Fixture([
        'toggle_target' => true,
        'setoran' => 100000,
        'metode' => 'ambil_sendiri',
        'tanggal_boleh_cair' => now()->addDays(7)->toDateString(),
    ]);

    Livewire::actingAs($admin)
        ->test(SerahTerimaPaket::class)
        ->assertViewHas('jumlahSiap', 1)
        ->assertViewHas('kepesertaan', fn ($daftar) => $daftar->count() === 1);
});

it('countdown dashboard menampilkan alasan saat belum lulus gerbang', function () {
    [$nasabah] = d17Fixture([
        'tanggal_boleh_cair' => now()->subDays(3)->toDateString(),
    ]);

    $this->actingAs($nasabah)->get(route('dashboard'))->assertOk()
        ->assertDontSee('Sudah Bisa Dicairkan')
        ->assertSee(KepesertaanPaket::ALASAN_TUNGGAKAN);
});

it('countdown dashboard mengikuti toggle target boleh cair', function () {
    [$nasabah] = d17Fixture([
        'toggle_target' => true,
        'setoran' => 100000,
        'tanggal_boleh_cair' => now()->addDays(15)->toDateString(),
    ]);

    $this->actingAs($nasabah)->get(route('dashboard'))->assertOk()
        ->assertSee('Sudah Bisa Dicairkan')
        ->assertDontSee('15 Hari Lagi');
});
