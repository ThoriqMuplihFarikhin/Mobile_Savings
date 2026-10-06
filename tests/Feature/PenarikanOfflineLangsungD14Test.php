<?php

use App\Actions\Penarikan\CatatPenarikanOfflineLangsungAction;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Models\AdminSetting;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

/**
 * Fixture kolektor penanggung jawab + nasabah mode offline bersaldo,
 * dengan kas fisik kolektor senilai $kas (0 = tanpa setoran masuk kas).
 *
 * @return array{kolektor: User, nasabah: User, produk: ProdukTabungan, setoran: TransaksiSetoran|null}
 */
function offlineKasP24(float $kas = 100000, float $saldo = 500000): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create([
        'no_hp' => null,
        'mode_akses' => 'offline',
        'pin_hash' => Hash::make(Str::random(40)),
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Offline D14',
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

    $setoran = null;

    if ($kas > 0) {
        $setoran = TransaksiSetoran::create([
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

    return compact('kolektor', 'nasabah', 'produk', 'setoran');
}

it('penarikan offline di bawah ambang langsung selesai memotong saldo kas dan mencatat log (D14)', function () {
    $fix = offlineKasP24();
    $admin = User::factory()->admin()->create();

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->set('catatan', 'Buku tabungan dicoret, tanda tangan di buku.')
        ->call('submit')
        ->assertSee('Penarikan offline langsung diselesaikan');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->jalur_pengajuan)->toBe('offline_kolektor')
        ->and($penarikan->metode_verifikasi)->toBe('tanpa_verifikasi_offline')
        ->and($penarikan->diverifikasi_oleh)->toBe($fix['kolektor']->id)
        ->and($penarikan->dibayar_oleh)->toBe($fix['kolektor']->id)
        ->and($penarikan->mempengaruhi_kas)->toBeTrue()
        ->and($penarikan->waktu_approval)->not->toBeNull()
        ->and($penarikan->waktu_pencairan)->not->toBeNull();

    $saldoAkhir = SaldoProduk::where('nasabah_id', $fix['nasabah']->id)
        ->where('produk_id', $fix['produk']->id)
        ->value('saldo');

    expect(bccomp((string) $saldoAkhir, '450000', 2))->toBe(0);

    // 100000 − 47500 (nominal_diterima) = 52500; komisi 2500 tetap di kas.
    expect(KasKolektorHitung::kasDiTangan($fix['kolektor']->id))->toBe('52500.00');

    expect(LogAktivitas::where('aksi', 'selesai_penarikan_offline')
        ->where('entitas_id', $penarikan->id)->exists())->toBeTrue()
        ->and(LogAktivitas::where('aksi', 'kas_berkurang_penarikan_tunai')
            ->where('entitas_id', $penarikan->id)->exists())->toBeTrue();

    expect(LogNotifikasi::where('nasabah_id', $admin->id)
        ->where('judul', 'Penarikan Offline Selesai')->exists())->toBeTrue()
        ->and(LogNotifikasi::where('nasabah_id', $fix['nasabah']->id)->count())->toBe(0);
});

it('penarikan offline di atas ambang dua approver masuk antrian approval admin (D14)', function () {
    $fix = offlineKasP24();

    User::factory()->admin()->create();
    User::factory()->admin()->create();
    User::factory()->admin()->create();
    AdminSetting::set('penarikan_batas_dua_approver', '30000');

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->set('catatan', 'Diambil di rumah, menunggu approval admin.')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->jalur_pengajuan)->toBe('offline')
        ->and(KasKolektorHitung::kasDiTangan($fix['kolektor']->id))->toBe('100000.00');

    $saldoAkhir = SaldoProduk::where('nasabah_id', $fix['nasabah']->id)
        ->where('produk_id', $fix['produk']->id)
        ->value('saldo');

    expect(bccomp((string) $saldoAkhir, '500000', 2))->toBe(0);
});

it('menolak kolektor yang bukan penanggung jawab nasabah offline (IDOR, D14)', function () {
    $fix = offlineKasP24();
    $kolektorLain = User::factory()->kolektor()->create();

    $this->actingAs($kolektorLain);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->assertSet('nasabahId', '')
        ->assertSee('Nasabah tidak valid');

    expect(TransaksiPenarikan::count())->toBe(0);

    expect(fn () => (new CatatPenarikanOfflineLangsungAction)->execute(
        $kolektorLain,
        $fix['nasabah'],
        $fix['produk'],
        50000,
        'kantor',
        'Catatan penarikan liar.',
    ))->toThrow(Exception::class, 'Nasabah ini bukan tanggung jawab Anda.');
});

it('pengaturan offline penarikan langsung selesai nonaktif mengembalikan ke antrian approval (D14)', function () {
    $fix = offlineKasP24();
    AdminSetting::set('offline_penarikan_langsung_selesai', 'false');

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->set('catatan', 'Pengaturan langsung selesai dimatikan.')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->jalur_pengajuan)->toBe('offline');
});

it('kas tidak cukup menjatuhkan penarikan langsung ke antrian approval (guard D13, D14)', function () {
    $fix = offlineKasP24(kas: 0);

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->set('catatan', 'Kas kolektor belum diisi.')
        ->call('submit')
        ->assertSee('masuk antrian approval admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->jalur_pengajuan)->toBe('offline');

    $saldoAkhir = SaldoProduk::where('nasabah_id', $fix['nasabah']->id)
        ->where('produk_id', $fix['produk']->id)
        ->value('saldo');

    expect(bccomp((string) $saldoAkhir, '500000', 2))->toBe(0);
});

it('mewajibkan catatan pada penarikan nasabah offline (D14)', function () {
    $fix = offlineKasP24();

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->call('submit')
        ->assertHasErrors(['catatan' => 'required']);

    expect(TransaksiPenarikan::count())->toBe(0);
});

it('penarikan offline langsung di kantor tidak menggerakkan kas kolektor (D14)', function () {
    $fix = offlineKasP24();

    $this->actingAs($fix['kolektor']);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $fix['nasabah']->id)
        ->set('produkId', (string) $fix['produk']->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'kantor')
        ->set('catatan', 'Diambil langsung di kantor.')
        ->call('submit')
        ->assertSee('Penarikan offline langsung diselesaikan');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->mempengaruhi_kas)->toBeFalse()
        ->and(KasKolektorHitung::kasDiTangan($fix['kolektor']->id))->toBe('100000.00');
});

it('penarikan kolektor untuk nasabah digital tetap masuk antrian approval tanpa catatan wajib (D14)', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = kolektorDenganKas();

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 500000]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', (string) $nasabah->id)
        ->set('produkId', (string) $produk->id)
        ->set('nominal', '50000')
        ->set('lokasi', 'rumah_kolektor')
        ->call('submit')
        ->assertSee('Menunggu persetujuan admin');

    $penarikan = TransaksiPenarikan::sole();

    expect($penarikan->status)->toBe('pending')
        ->and($penarikan->jalur_pengajuan)->toBe('offline');
});
