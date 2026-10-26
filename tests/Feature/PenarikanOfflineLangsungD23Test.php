<?php

use App\Actions\Penarikan\CatatPenarikanOfflineLangsungAction;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

/**
 * Fixture kolektor penanggung jawab + nasabah mode offline bersaldo,
 * dengan kas fisik kolektor senilai $kas (0 = tanpa setoran masuk kas).
 *
 * @return array{kolektor: User, nasabah: User, produk: ProdukTabungan}
 */
function offlineD23(float $kas = 100000, float $saldo = 500000): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
        'pin_hash' => Hash::make(Str::random(40)),
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Offline D23',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $saldo,
    ]);

    if ($kas > 0) {
        TransaksiSetoran::create([
            'nasabah_id' => $nasabah->id,
            'produk_id' => $produk->id,
            'nominal' => $kas,
            'tanggal_transaksi' => now()->toDateString(),
            'tanggal_input_sistem' => now(),
            'input_by' => $kolektor->id,
            'sumber_input' => 'real_time',
            'status' => 'tercatat',
        ]);
    }

    return compact('kolektor', 'nasabah', 'produk');
}

/**
 * Gambar bukti valid (PNG/JPEG) berformat data URL.
 */
function buktiD23(string $mime = 'png'): string
{
    $image = imagecreatetruecolor(8, 8);
    ob_start();
    $mime === 'png' ? imagepng($image) : imagejpeg($image);
    $raw = ob_get_clean();
    imagedestroy($image);

    return 'data:image/'.$mime.';base64,'.base64_encode($raw);
}

function isiPenarikanD23($component, array $fix, array $opsi = [])
{
    return $component
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', (string) ($opsi['nominal'] ?? '50000'))
        ->set('lokasi', $opsi['lokasi'] ?? 'rumah_kolektor')
        ->set('catatan', $opsi['catatan'] ?? 'Buku tabungan dicoret di rumah nasabah.')
        ->set('buktiBase64', $opsi['bukti'] ?? buktiD23())
        ->set('buktiJenis', $opsi['jenis'] ?? 'tanda_tangan');
}

it('menambah kolom bukti, tinjauan, dan izin kolektor beserta indeksnya (P10.1)', function () {
    expect(Schema::hasColumn('transaksi_penarikan', 'bukti_path'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'bukti_jenis'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'catatan_kolektor'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'ditinjau_oleh'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'ditinjau_pada'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'hasil_tinjauan'))->toBeTrue()
        ->and(Schema::hasColumn('transaksi_penarikan', 'catatan_tinjauan'))->toBeTrue()
        ->and(Schema::hasColumn('users', 'izin_penarikan_langsung'))->toBeTrue();

    expect(Schema::hasIndex('transaksi_penarikan', ['metode_verifikasi', 'hasil_tinjauan']))->toBeTrue();
});

it('kolektor baru diberi izin penarikan langsung secara bawaan (D23)', function () {
    $kolektor = User::factory()->kolektor()->create();

    expect($kolektor->izin_penarikan_langsung)->toBeTrue();
});

it('dengan setelan default seluruh penarikan offline masuk antrian dan menyimpan bukti (D23)', function () {
    $fix = offlineD23();

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix)
        ->call('submit')
        ->assertSee('Penarikan offline berhasil diajukan')
        ->assertHasNoErrors();

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->jalur_pengajuan)->toBe('offline')
        ->and($penarikan->hasil_tinjauan)->toBeNull()
        ->and($penarikan->bukti_path)->not->toBeNull()
        ->and($penarikan->bukti_jenis)->toBe('tanda_tangan')
        ->and($penarikan->catatan_kolektor)->toBe('Buku tabungan dicoret di rumah nasabah.')
        ->and(Storage::disk('local')->exists((string) $penarikan->bukti_path))->toBeTrue()
        ->and(Storage::disk('local')->allFiles('penarikan'))->toHaveCount(1);
});

it('pengambilan di kantor tidak pernah langsung selesai meski batas diisi (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['lokasi' => 'kantor'])
        ->assertSee('akan menunggu persetujuan admin: pengambilan di kantor')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->mempengaruhi_kas)->toBeFalse();
});

it('nominal di atas batas penarikan langsung jatuh ke antrian (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '40000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '50000'])
        ->assertSee('akan menunggu persetujuan admin: nominal melebihi batas')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    expect(TransaksiPenarikan::sole()->status)->toBe('pending');
});

it('total penarikan langsung hari ini dibatasi batas harian (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');
    AdminSetting::set('offline_penarikan_batas_harian', '60000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '50000'])
        ->call('submit')
        ->assertSee('Penarikan offline langsung diselesaikan');

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '20000'])
        ->assertSee('akan menunggu persetujuan admin: melebihi batas penarikan langsung hari ini')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    $status = TransaksiPenarikan::orderBy('id')->pluck('status')->all();

    expect($status)->toBe(['selesai', 'pending']);
});

it('kolektor yang dibekukan tidak bisa menyelesaikan penarikan langsung (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');
    $fix['kolektor']->update(['izin_penarikan_langsung' => false]);

    $this->actingAs($fix['kolektor']->fresh());

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '50000'])
        ->assertSee('akan menunggu persetujuan admin: izin penarikan langsung')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    expect(TransaksiPenarikan::sole()->status)->toBe('pending');
});

it('penarikan offline tanpa bukti ditolak dan tidak membuat baris (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['bukti' => ''])
        ->call('submit')
        ->assertHasErrors(['buktiBase64']);

    expect(TransaksiPenarikan::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();

    expect(fn () => (new CatatPenarikanOfflineLangsungAction)->execute(
        $fix['kolektor'],
        $fix['nasabah'],
        $fix['produk'],
        50000,
        'rumah_kolektor',
        'Tanpa bukti sama sekali.',
    ))->toThrow(Exception::class, 'Bukti penarikan');

    expect(TransaksiPenarikan::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('menolak bukti yang bukan gambar dan yang melebihi 1,5 MB (D24)', function () {
    $fix = offlineD23();

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, [
        'bukti' => 'data:image/png;base64,'.base64_encode('bukan gambar'),
    ])->call('submit')->assertHasErrors(['buktiBase64']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, [
        'bukti' => 'data:image/png;base64,'.base64_encode(random_bytes(1600000)),
    ])->call('submit')->assertHasErrors(['buktiBase64']);

    expect(TransaksiPenarikan::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('menyimpan bukti pada disk privat dengan nama acak (D24)', function () {
    $fix = offlineD23();

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['jenis' => 'foto'])
        ->call('submit');

    $penarikan = TransaksiPenarikan::sole();
    $files = Storage::disk('local')->allFiles('penarikan');

    expect($penarikan->bukti_jenis)->toBe('foto')
        ->and($files)->toHaveCount(1)
        ->and($files[0])->toMatch('/^penarikan\/bukti-[0-9a-f-]{36}\.(png|jpg)$/')
        ->and($penarikan->bukti_path)->toBe($files[0]);

    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('tidak meninggalkan berkas bukti yatim bila perekaman gagal (D24)', function () {
    $fix = offlineD23();

    $this->actingAs($fix['kolektor']);

    TransaksiPenarikan::creating(function (): void {
        throw new RuntimeException('simulasi kegagalan database');
    });

    try {
        isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix)->call('submit');
    } finally {
        TransaksiPenarikan::flushEventListeners();
    }

    expect(TransaksiPenarikan::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty()
        ->and(Storage::disk('public')->allFiles())->toBeEmpty();
});

it('menampilkan pratinjau keputusan sebelum kirim tanpa menulis ke database (D23)', function () {
    $fix = offlineD23();

    $this->actingAs($fix['kolektor']);

    // Setelan default: batas nol → semua ditahan.
    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix)
        ->assertSee('akan menunggu persetujuan admin: batas penarikan langsung belum diatur')
        ->assertHasNoErrors();

    expect(TransaksiPenarikan::count())->toBe(0)
        ->and(Storage::disk('local')->allFiles())->toBeEmpty();

    // Batas diisi + seluruh syarat terpenuhi → pratinjau langsung.
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix)
        ->assertSee('akan diproses langsung');

    expect(TransaksiPenarikan::count())->toBe(0);
});

it('pratinjau meminta bukti sebelum pengiriman (D24)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['bukti' => ''])
        ->assertSee('Bukti penarikan wajib dilampirkan');

    expect(TransaksiPenarikan::count())->toBe(0);
});

it('kas kurang menjatuhkan penarikan yang lolos batas ke antrian (guard D13, D23)', function () {
    $fix = offlineD23(kas: 0);
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '50000'])
        ->call('submit')
        ->assertSee('masuk antrian approval admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and(KasKolektorHitung::kasDiTangan($fix['kolektor']->id))->toBe('0.00');
});

it('penarikan offline yang lolos seluruh syarat langsung selesai (D23)', function () {
    $fix = offlineD23();
    AdminSetting::set('offline_penarikan_batas_langsung', '100000');
    $admin = User::factory()->admin()->create();

    $this->actingAs($fix['kolektor']);

    isiPenarikanD23(Livewire::test(PenarikanOffline::class), $fix, ['nominal' => '50000'])
        ->call('submit')
        ->assertSee('Penarikan offline langsung diselesaikan');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->jalur_pengajuan)->toBe('offline_kolektor')
        ->and($penarikan->metode_verifikasi)->toBe('tanpa_verifikasi_offline')
        ->and($penarikan->hasil_tinjauan)->toBeNull()
        ->and($penarikan->catatan_kolektor)->toBe('Buku tabungan dicoret di rumah nasabah.')
        ->and($penarikan->bukti_path)->not->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($fix['kolektor']->id))->toBe('52500.00');

    expect(LogNotifikasi::where('nasabah_id', $admin->id)
        ->where('judul', 'Penarikan Offline Selesai')->exists())->toBeTrue();
});
