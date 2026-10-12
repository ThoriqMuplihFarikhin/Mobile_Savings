<?php

use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\KelolaKolektor;
use App\Livewire\Admin\Laporan;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Fixture kolektor + nasabah offline + penarikan buatan berstatus selesai
 * (langsung maupun lewat PIN) untuk menguji integrasi P10.1: toggle izin di
 * KelolaKolektor, tautan bukti di ApprovalPenarikan, seksi laporan penarikan
 * offline, dan penanda di RekonsiliasiKas/SetorKantor.
 *
 * @return array{kolektor: User, nasabah: User, produk: ProdukTabungan}
 */
function offlineIntegrasiD27(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
        'pin_hash' => Hash::make(Str::random(40)),
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Integrasi D27',
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
        'saldo' => 500000,
    ]);

    return compact('kolektor', 'nasabah', 'produk');
}

/**
 * Baris penarikan buatan dengan opsi lengkap (default: penarikan offline
 * langsung selesai tanpa tinjauan, mempengaruhi kas, dengan bukti).
 */
function buatPenarikanD27(array $fix, array $opsi = []): TransaksiPenarikan
{
    $bukti = array_key_exists('bukti', $opsi) ? $opsi['bukti'] : 'penarikan/bukti-uji.png';

    return TransaksiPenarikan::create([
        'nasabah_id' => $fix['nasabah']->id,
        'produk_id' => $fix['produk']->id,
        'nominal_diminta' => $opsi['diminta'] ?? 50000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => $opsi['diterima'] ?? 50000,
        'jalur_pengajuan' => $opsi['jalur'] ?? 'offline_kolektor',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => $opsi['status'] ?? 'selesai',
        'metode_verifikasi' => $opsi['metode'] ?? 'tanpa_verifikasi_offline',
        'diverifikasi_oleh' => $fix['kolektor']->id,
        'dibayar_oleh' => $fix['kolektor']->id,
        'mempengaruhi_kas' => $opsi['mempengaruhi_kas'] ?? true,
        'waktu_approval' => now(),
        'waktu_pencairan' => now(),
        'hasil_tinjauan' => $opsi['tinjauan'] ?? null,
        'bukti_path' => $bukti,
        'bukti_jenis' => $bukti !== null ? 'tanda_tangan' : null,
    ]);
}

it('admin bisa mengubah izin penarikan langsung kolektor dengan log (D23)', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleIzinPenarikanLangsung', $kolektor->id);

    expect($kolektor->fresh()->izin_penarikan_langsung)->toBeFalse()
        ->and(LogAktivitas::where('aksi', 'ubah_izin_penarikan_langsung')
            ->where('entitas_terkait', 'users')
            ->where('entitas_id', $kolektor->id)
            ->exists())->toBeTrue();

    Livewire::test(KelolaKolektor::class)
        ->call('toggleIzinPenarikanLangsung', $kolektor->id);

    expect($kolektor->fresh()->izin_penarikan_langsung)->toBeTrue();
});

it('toggle izin penarikan langsung mengabaikan id yang bukan kolektor (D23)', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->call('toggleIzinPenarikanLangsung', $nasabah->id);

    expect($nasabah->fresh()->izin_penarikan_langsung)->toBeTrue()
        ->and(LogAktivitas::where('aksi', 'ubah_izin_penarikan_langsung')->exists())->toBeFalse();
});

it('halaman kelola kolektor menampilkan status izin penarikan langsung (D23)', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $kolektor->update(['izin_penarikan_langsung' => false]);

    $this->actingAs($admin);

    Livewire::test(KelolaKolektor::class)
        ->assertSee('Penarikan Langsung')
        ->assertSee('toggleIzinPenarikanLangsung('.$kolektor->id.')', false);
});

it('approval penarikan menampilkan tautan bukti untuk penarikan offline (D24)', function () {
    $fix = offlineIntegrasiD27();
    $denganBukti = buatPenarikanD27($fix, ['status' => 'pending', 'mempengaruhi_kas' => false]);
    $tanpaBukti = buatPenarikanD27($fix, [
        'status' => 'pending',
        'mempengaruhi_kas' => false,
        'bukti' => null,
    ]);
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->assertSee(route('penarikan.bukti', $denganBukti), false)
        ->assertSee('Lihat bukti');

    Livewire::test(ApprovalPenarikan::class)
        ->assertDontSee(route('penarikan.bukti', $tanpaBukti), false);
});

it('seksi laporan penarikan offline menghitung rekap per kolektor dan persentase kas (D23)', function () {
    $a = offlineIntegrasiD27();
    $b = offlineIntegrasiD27();

    buatPenarikanD27($a, ['diterima' => 50000, 'tinjauan' => null]);
    buatPenarikanD27($a, ['diterima' => 50000, 'metode' => 'pin_nasabah', 'jalur' => 'offline']);
    buatPenarikanD27($b, ['diterima' => 30000, 'tinjauan' => 'sesuai']);
    buatPenarikanD27($b, ['diterima' => 20000, 'tinjauan' => 'bermasalah']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $tes = Livewire::test(Laporan::class)->call('pilihSeksi', 'offline');

    expect($tes->get('seksi'))->toBe('offline');

    $rows = collect($tes->viewData('offlineRows'));

    $barisA = $rows->firstWhere('kolektor_id', $a['kolektor']->id);
    $barisB = $rows->firstWhere('kolektor_id', $b['kolektor']->id);

    expect($barisA['jumlah'])->toBe(1)
        ->and($barisA['total'])->toBe(50000.0)
        ->and($barisA['persen_kas'])->toBe(50.0)
        ->and($barisA['belum'])->toBe(1)
        ->and($barisA['sesuai'])->toBe(0)
        ->and($barisA['bermasalah'])->toBe(0)
        ->and($barisB['jumlah'])->toBe(2)
        ->and($barisB['total'])->toBe(50000.0)
        ->and($barisB['persen_kas'])->toBe(100.0)
        ->and($barisB['belum'])->toBe(0)
        ->and($barisB['sesuai'])->toBe(1)
        ->and($barisB['bermasalah'])->toBe(1);
});

it('baris detail laporan penarikan offline menautkan bukti (D24)', function () {
    $fix = offlineIntegrasiD27();
    $penarikan = buatPenarikanD27($fix, ['diterima' => 40000, 'tinjauan' => 'sesuai']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $tes = Livewire::test(Laporan::class)->call('pilihSeksi', 'offline');

    $detail = collect($tes->viewData('offlineDetail'));

    expect($detail)->toHaveCount(1)
        ->and($detail->first()['id'])->toBe($penarikan->id)
        ->and($detail->first()['tinjauan'])->toBe('Sesuai');

    $tes->assertSee(route('penarikan.bukti', $penarikan), false)
        ->assertSee('Lihat bukti');
});

it('seksi laporan penarikan offline menampilkan tab dan judul (D23)', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    Livewire::test(Laporan::class)
        ->assertSee('Penarikan Offline');
});

it('ekspor CSV laporan penarikan offline memakai nama file dan CsvSafe (D23)', function () {
    $fix = offlineIntegrasiD27();
    $fix['nasabah']->update(['name' => '=BUY-TRICK()']);
    buatPenarikanD27($fix, ['diterima' => 50000, 'tinjauan' => 'sesuai']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $tes = Livewire::test(Laporan::class)->call('pilihSeksi', 'offline');
    $tes->call('exportCsv')
        ->assertFileDownloaded('laporan-penarikan-offline-harian-'.now()->toDateString().'.csv');

    $csv = base64_decode((string) data_get($tes->effects, 'download.content'));

    expect($csv)->toContain('REKAP PER KOLEKTOR')
        ->toContain('DETAIL PENARIKAN')
        ->toContain("'=BUY-TRICK()");
});

it('rekonsiliasi kas menandai penarikan langsung pada rincian kolektor (D25)', function () {
    $fix = offlineIntegrasiD27();
    buatPenarikanD27($fix, ['diterima' => 50000, 'tinjauan' => null]);
    buatPenarikanD27($fix, ['diterima' => 20000, 'tinjauan' => 'sesuai']);
    buatPenarikanD27($fix, ['diterima' => 10000, 'tinjauan' => 'bermasalah']);
    buatPenarikanD27($fix, ['diterima' => 70000, 'metode' => 'pin_nasabah', 'jalur' => 'offline']);

    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $tes = Livewire::test(RekonsiliasiKas::class)->set('kolektorId', $fix['kolektor']->id);

    $rincian = $tes->get('rincianLangsung');

    expect($rincian['belum']['jumlah'])->toBe(1)
        ->and($rincian['sesuai']['jumlah'])->toBe(1)
        ->and($rincian['bermasalah']['jumlah'])->toBe(1)
        ->and($rincian['sesuai']['nominal'])->toBe(20000.0);

    $tes->assertSee('penarikan offline langsung')
        ->assertSee('belum ditinjau');
});

it('halaman setor kantor menandai penarikan langsung pada ringkas kas (D25)', function () {
    $fix = offlineIntegrasiD27();
    buatPenarikanD27($fix, ['diterima' => 50000, 'tinjauan' => null]);
    buatPenarikanD27($fix, ['diterima' => 30000, 'tinjauan' => 'bermasalah']);
    buatPenarikanD27($fix, ['diterima' => 10000, 'metode' => 'pin_nasabah', 'jalur' => 'offline']);

    $this->actingAs($fix['kolektor']);

    $tes = Livewire::test(SetorKantor::class);

    $rincian = $tes->get('rincianLangsung');

    expect($rincian['belum']['jumlah'])->toBe(1)
        ->and($rincian['bermasalah']['jumlah'])->toBe(1)
        ->and($rincian['sesuai']['jumlah'])->toBe(0);

    $tes->assertSee('penarikan offline langsung')
        ->assertSee('belum ditinjau');
});
