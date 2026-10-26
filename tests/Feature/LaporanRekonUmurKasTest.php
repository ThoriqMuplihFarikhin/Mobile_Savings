<?php

use App\Livewire\Admin\Laporan;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, kolektor: User, produk: ProdukTabungan}
 */
function seedLaporanRekon(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'produk');
}

it('merangkum rekonsiliasi per kolektor dan menampilkan detail pengajuan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor] = seedLaporanRekon();

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'total_diterima' => 100000,
        'selisih' => 0,
        'status' => 'cocok',
        'diterima_oleh' => $admin->id,
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->subDay()->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 55000,
        'selisih' => 5000,
        'keterangan_selisih' => 'kelebihan setor',
        'status' => 'lebih',
        'diterima_oleh' => $admin->id,
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDay()->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('pilihSeksi', 'rekon')
        ->assertSee('Definisi angka')
        ->assertSee('Penerima');

    $baris = collect($component->viewData('rekonRows'))->firstWhere('kolektor_id', $kolektor->id);
    expect($baris)->not->toBeNull()
        ->and($baris['jumlah'])->toBe(2)
        ->and($baris['seharusnya'])->toBe(150000.0)
        ->and($baris['diterima'])->toBe(155000.0)
        ->and($baris['selisih'])->toBe(5000.0);

    $detail = collect($component->viewData('rekonDetail'));
    expect($detail)->toHaveCount(2);
    $bermasalah = $detail->firstWhere('keterangan_selisih', 'kelebihan setor');
    expect($bermasalah)->not->toBeNull()
        ->and($bermasalah->diterimaOleh->name)->toBe($admin->name);
});

it('mengelompokkan umur kas kolektor 0-1 2-3 dan lebih dari 3 hari', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'produk' => $produk] = seedLaporanRekon();
    $nasabah = User::factory()->nasabah()->create();

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 60000,
        'tanggal_transaksi' => now()->subDays(5)->toDateString(),
        'tanggal_input_sistem' => now()->subDays(5),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => 'tercatat',
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'umurkas')
        ->assertSee('Definisi angka')
        ->assertSee('>3 hari')
        ->assertSee('Umur Terlama');

    $rows = collect($component->viewData('umurKasRows'));
    $baris = $rows->firstWhere('kolektor_id', $kolektor->id);
    expect($baris)->not->toBeNull()
        ->and($baris['kas_di_tangan'])->toBe(60000.0)
        ->and($baris['umur_terlama_hari'])->toBe(5);

    $kelompok = collect($component->viewData('umurKelompok'));
    expect($kelompok['>3 hari']['jumlah'])->toBe(1)
        ->and($kelompok['>3 hari']['kas'])->toBe(60000.0)
        ->and($kelompok['0-1 hari']['jumlah'])->toBe(0);
});

it('mengekspor csv rekonsiliasi dan umur kas', function () {
    ['admin' => $admin, 'kolektor' => $kolektor] = seedLaporanRekon();

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'total_diterima' => 99000,
        'selisih' => -1000,
        'keterangan_selisih' => 'kurang setor',
        'status' => 'kurang',
        'diterima_oleh' => $admin->id,
    ]);

    $this->actingAs($admin);

    $rekon = Livewire::test(Laporan::class)->call('pilihSeksi', 'rekon');
    $rekon->call('exportCsv')->assertFileDownloaded(
        'laporan-rekon-harian-'.now()->toDateString().'.csv'
    );
    $csvRekon = base64_decode((string) data_get($rekon->effects, 'download.content'));
    expect($csvRekon)->toContain('Seharusnya')
        ->and($csvRekon)->toContain('Penerima')
        ->and($csvRekon)->toContain('kurang setor');

    $umur = Livewire::test(Laporan::class)->call('pilihSeksi', 'umurkas');
    $umur->call('exportCsv')->assertFileDownloaded('laporan-umur-kas-'.now()->toDateString().'.csv');
    $csvUmur = base64_decode((string) data_get($umur->effects, 'download.content'));
    expect($csvUmur)->toContain('Kelompok')
        ->and($csvUmur)->toContain('Umur Terlama (hari)');
});
