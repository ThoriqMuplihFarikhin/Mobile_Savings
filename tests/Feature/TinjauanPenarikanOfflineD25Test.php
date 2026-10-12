<?php

use App\Livewire\Admin\Pengaturan;
use App\Livewire\Admin\TinjauanPenarikanOffline;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Support\AntreanPersetujuan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

/**
 * Penarikan offline langsung historis (hasil dari kolektor) + seluruh relasinya.
 * Dipakai untuk menguji antrian tinjauan D25 dan akses berkas bukti D24.
 *
 * @return array{kolektor: User, nasabah: User, produk: ProdukTabungan, penarikan: TransaksiPenarikan}
 */
function penarikanLangsungD25(?string $buktiPath = 'penarikan/bukti-uji.png'): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
        'pin_hash' => Hash::make(Str::random(40)),
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Tinjauan D25',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
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
        'saldo' => 450000,
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 50000,
        'jalur_pengajuan' => 'offline_kolektor',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'selesai',
        'metode_verifikasi' => 'tanpa_verifikasi_offline',
        'diverifikasi_oleh' => $kolektor->id,
        'dibayar_oleh' => $kolektor->id,
        'mempengaruhi_kas' => true,
        'waktu_approval' => now(),
        'waktu_pencairan' => now(),
        'bukti_path' => $buktiPath,
        'bukti_jenis' => $buktiPath !== null ? 'tanda_tangan' : null,
        'catatan_kolektor' => 'Buku tabungan dicoret di rumah nasabah.',
    ]);

    if ($buktiPath !== null) {
        Storage::disk('local')->put($buktiPath, (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));
    }

    return compact('kolektor', 'nasabah', 'produk', 'penarikan');
}

it('menampilkan seluruh penarikan offline lama sebagai backlog tinjauan (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    expect($fix['penarikan']->hasil_tinjauan)->toBeNull()
        ->and(AntreanPersetujuan::ringkas()['tinjauan'])->toBe(1);

    $this->actingAs($admin)
        ->get(route('admin.tinjauan-penarikan-offline'))
        ->assertOk()
        ->assertSee('data-test="baris-tinjauan-'.$fix['penarikan']->id.'"', false)
        ->assertSee('Belum ditinjau');
});

it('menandai penarikan sesuai beserta log aktivitas (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TinjauanPenarikanOffline::class)
        ->call('sesuai', $fix['penarikan']->id)
        ->assertHasNoErrors()
        ->assertSee('Penarikan ditandai sesuai');

    $fix['penarikan']->refresh();

    expect($fix['penarikan']->hasil_tinjauan)->toBe('sesuai')
        ->and($fix['penarikan']->ditinjau_oleh)->toBe($admin->id)
        ->and($fix['penarikan']->ditinjau_pada)->not->toBeNull()
        ->and($fix['kolektor']->fresh()->izin_penarikan_langsung)->toBeTrue();

    expect(LogAktivitas::where('aksi', 'tinjau_penarikan_offline')
        ->where('entitas_id', $fix['penarikan']->id)->exists())->toBeTrue()
        ->and(AntreanPersetujuan::ringkas()['tinjauan'])->toBe(0);
});

it('penandaan ganda tidak menimpa hasil tinjauan pertama (D25)', function () {
    $fix = penarikanLangsungD25();
    $adminPertama = User::factory()->admin()->create();
    $adminKedua = User::factory()->admin()->create();

    $this->actingAs($adminPertama);
    Livewire::test(TinjauanPenarikanOffline::class)
        ->call('sesuai', $fix['penarikan']->id);

    $this->actingAs($adminKedua);
    Livewire::test(TinjauanPenarikanOffline::class)
        ->call('sesuai', $fix['penarikan']->id)
        ->assertSee('sudah pernah ditinjau');

    $fix['penarikan']->refresh();

    expect($fix['penarikan']->hasil_tinjauan)->toBe('sesuai')
        ->and($fix['penarikan']->ditinjau_oleh)->toBe($adminPertama->id);
});

it('menolak penandaan bermasalah tanpa alasan minimal 10 karakter (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TinjauanPenarikanOffline::class)
        ->set('catatanTinjauan', 'pendek')
        ->call('bermasalah', $fix['penarikan']->id)
        ->assertHasErrors(['catatanTinjauan']);

    expect($fix['penarikan']->fresh()->hasil_tinjauan)->toBeNull();
});

it('penandaan bermasalah membekukan penarikan langsung kolektor dan memberi tahu (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TinjauanPenarikanOffline::class)
        ->set('catatanTinjauan', 'Nominal tidak cocok dengan bukti tanda tangan.')
        ->call('bermasalah', $fix['penarikan']->id)
        ->assertHasNoErrors()
        ->assertSee('Penarikan ditandai bermasalah');

    $fix['penarikan']->refresh();

    expect($fix['penarikan']->hasil_tinjauan)->toBe('bermasalah')
        ->and($fix['penarikan']->catatan_tinjauan)->toBe('Nominal tidak cocok dengan bukti tanda tangan.')
        ->and($fix['kolektor']->fresh()->izin_penarikan_langsung)->toBeFalse();

    expect(LogNotifikasi::where('nasabah_id', $fix['kolektor']->id)
        ->where('judul', 'Penarikan Offline Bermasalah')->exists())->toBeTrue();
});

it('pembekuan bisa dilepas saat admin tidak mencentang opsi (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TinjauanPenarikanOffline::class)
        ->set('catatanTinjauan', 'Bukti buram, minta kolektor mengulang.')
        ->set('bekukanKolektor', false)
        ->call('bermasalah', $fix['penarikan']->id);

    expect($fix['kolektor']->fresh()->izin_penarikan_langsung)->toBeTrue()
        ->and($fix['penarikan']->fresh()->hasil_tinjauan)->toBe('bermasalah');
});

it('menampilkan bukti hanya untuk admin dan kolektor pembuatnya (IDOR, D24)', function () {
    $fix = penarikanLangsungD25();
    $kolektorLain = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertOk();

    expect(strtolower((string) $this->get(route('penarikan.bukti', $fix['penarikan']))
        ->baseResponse->headers->get('Cache-Control')))->toContain('no-store', 'private', 'max-age=0');

    $this->actingAs($fix['kolektor'])
        ->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertOk();

    $this->actingAs($kolektorLain)
        ->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertForbidden();

    $this->actingAs($nasabah)
        ->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertForbidden();

    auth()->logout();

    $this->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertRedirect();
});

it('menjawab 404 bila berkas bukti penarikan tidak ada (D24)', function () {
    $fix = penarikanLangsungD25(buktiPath: null);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('penarikan.bukti', $fix['penarikan']))
        ->assertNotFound();
});

it('menolak kolektor membuka halaman tinjauan (D25)', function () {
    $this->actingAs(User::factory()->kolektor()->create())
        ->get(route('admin.tinjauan-penarikan-offline'))
        ->assertForbidden();
});

it('menautkan antrean tinjauan dari hub persetujuan beserta lengkap (D25)', function () {
    penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertSee('data-test="hitung-tinjauan"', false)
        ->assertSee(route('admin.tinjauan-penarikan-offline'));
});

it('lencana bottom-nav menghitung penarikan offline yang belum ditinjau (D25)', function () {
    penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.persetujuan.index'))
        ->assertOk()
        ->assertSee('data-test="bottom-nav-badge"', false);

    expect(AntreanPersetujuan::total())->toBeGreaterThan(0);
});

it('filter status dan kolektor menyaring daftar tinjauan (D25)', function () {
    $fix = penarikanLangsungD25();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(TinjauanPenarikanOffline::class)
        ->set('kolektorFilter', (string) $fix['kolektor']->id)
        ->assertSee($fix['nasabah']->name)
        ->set('kolektorFilter', (string) User::factory()->kolektor()->create()->id)
        ->assertSee('Tidak ada penarikan offline pada filter ini.')
        ->set('kolektorFilter', (string) $fix['kolektor']->id)
        ->set('statusFilter', 'sesuai')
        ->assertSee('Tidak ada penarikan offline pada filter ini.')
        ->set('statusFilter', 'belum')
        ->assertDontSee('Tidak ada penarikan offline pada filter ini.');
});

it('pengaturan batas penarikan offline tersimpan dari halaman pengaturan (D23)', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(Pengaturan::class)
        ->call('setTab', 'umum')
        ->set('offlineBatasLangsung', '150000')
        ->set('offlineBatasHarian', '300000')
        ->call('simpanKonfigurasi')
        ->assertHasNoErrors();

    expect(AdminSetting::get('offline_penarikan_batas_langsung'))->toBe('150000')
        ->and(AdminSetting::get('offline_penarikan_batas_harian'))->toBe('300000');
});
