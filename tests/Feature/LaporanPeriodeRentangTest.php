<?php

use App\Livewire\Admin\Laporan;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, kolektor: User, nasabah: User, produk: ProdukTabungan}
 */
function seedLaporanRentang(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function setoranRentang(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal, string $tanggal): TransaksiSetoran
{
    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => 'tercatat',
    ]);
}

it('menjumlahkan setoran dalam periode rentang tanggal', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanRentang();

    setoranRentang($kolektor, $nasabah, $produk, 20000, now()->subDays(3)->toDateString());
    setoranRentang($kolektor, $nasabah, $produk, 30000, now()->subDay()->toDateString());
    setoranRentang($kolektor, $nasabah, $produk, 40000, now()->subDays(10)->toDateString());

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDays(3)->toDateString())
        ->set('sampaiTanggal', now()->toDateString());

    expect((float) $component->viewData('totalSetoran'))->toBe(50000.0)
        ->and((int) $component->viewData('jumlahTransaksiSetoran'))->toBe(2)
        ->and($component->viewData('totalPenarikan'))->toBe(0.0);
});

it('menolak sampai sebelum dari dan mengosongkan laporan rentang', function () {
    ['admin' => $admin] = seedLaporanRentang();

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->toDateString())
        ->set('sampaiTanggal', now()->subDays(2)->toDateString())
        ->assertHasErrors(['sampaiTanggal']);

    expect((float) $component->viewData('totalSetoran'))->toBe(0.0);

    $component->call('exportCsv')
        ->assertHasErrors(['sampaiTanggal'])
        ->assertNoFileDownloaded();
});

it('menamai file laporan rentang dengan kedua tanggal', function () {
    ['admin' => $admin] = seedLaporanRentang();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDays(3)->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('exportCsv')
        ->assertFileDownloaded(
            'laporan-rentang-'.now()->subDays(3)->toDateString().'-'.now()->toDateString().'.csv'
        );
});

it('menambahkan kolom d13 pada laporan kolektor dan csv', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanRentang();

    setoranRentang($kolektor, $nasabah, $produk, 50000, now()->toDateString());
    setoranRentang($kolektor, $nasabah, $produk, 70000, now()->subMonth()->toDateString());

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 50000,
        'selisih' => 0,
        'status' => 'cocok',
    ]);

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 4210,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 210,
        'nominal_diterima' => 4000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'selesai',
        'disetujui_oleh' => $admin->id,
        'waktu_approval' => now(),
        'dibayar_oleh' => $kolektor->id,
        'mempengaruhi_kas' => true,
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)->call('pilihSeksi', 'kolektor');

    $component
        ->assertSee('Penarikan Tunai Dibayar')
        ->assertSee('Kas di Tangan')
        ->assertSee('Setor Kantor');

    $baris = collect($component->viewData('kolektorRows'))->firstWhere('nama', $kolektor->name);
    expect($baris)->not->toBeNull()
        ->and($baris['penarikan_dibayar'])->toBe(4000.0)
        ->and($baris['kas_di_tangan'])->toBe(116000.0)
        ->and($baris['setor_kantor'])->toBe(50000.0)
        ->and($baris['total_setoran'])->toBe(50000.0);

    $component->call('exportCsv');
    $csv = base64_decode((string) data_get($component->effects, 'download.content'));
    expect($csv)->toContain('Kas di Tangan')
        ->and($csv)->toContain('Penarikan Tunai Dibayar')
        ->and($csv)->toContain('Setor Kantor');
});

it('mencatat log ekspor_laporan dengan seksi dan periode', function () {
    ['admin' => $admin] = seedLaporanRentang();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDays(3)->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('exportCsv');

    $log = LogAktivitas::where('aksi', 'ekspor_laporan')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->detail['seksi'])->toBe('keuangan')
        ->and($log->detail['periode'])->toBe('rentang')
        ->and($log->detail['dari'])->toBe(now()->subDays(3)->toDateString());
});

it('menampilkan definisi angka pada tiap seksi laporan', function () {
    ['admin' => $admin] = seedLaporanRentang();

    $this->actingAs($admin);

    Livewire::test(Laporan::class)
        ->assertSee('Definisi angka')
        ->call('pilihSeksi', 'kolektor')
        ->assertSee('Definisi angka')
        ->call('pilihSeksi', 'paket')
        ->assertSee('Definisi angka')
        ->call('pilihSeksi', 'barang')
        ->assertSee('Definisi angka');
});
