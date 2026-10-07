<?php

use App\Livewire\Admin\Laporan;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, nasabah: User, kolektor: User, produk: ProdukTabungan}
 */
function seedLaporanMutasiPenarikan(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $kolektor = User::factory()->kolektor()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'nasabah', 'kolektor', 'produk');
}

it('menampilkan mutasi nasabah dengan saldo berjalan', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'kolektor' => $kolektor, 'produk' => $produk] = seedLaporanMutasiPenarikan();

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 150000]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 30000,
        'tanggal_transaksi' => now()->subDay()->toDateString(),
        'tanggal_input_sistem' => now()->subDay(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => 'dibatalkan',
    ]);
    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 40000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2000,
        'nominal_diterima' => 38000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'selesai',
        'waktu_approval' => now()->setTime(10, 0, 0),
        'mempengaruhi_kas' => true,
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'mutasi')
        ->set('mutasiNasabahId', $nasabah->id)
        ->assertSee('Definisi angka');

    $rows = collect($component->viewData('mutasiRows'));
    expect($rows)->toHaveCount(2)
        ->and($rows[0]['tipe'])->toBe('Setoran')
        ->and($rows[0]['saldo'])->toBe(190000.0)
        ->and($rows[1]['tipe'])->toBe('Penarikan')
        ->and($rows[1]['saldo'])->toBe(150000.0);

    $ringkas = $component->viewData('mutasiRingkas');
    expect($ringkas['saldoAwal'])->toBe(140000.0)
        ->and($ringkas['saldoAkhir'])->toBe(150000.0)
        ->and($ringkas['totalSetoran'])->toBe(50000.0)
        ->and($ringkas['totalPenarikan'])->toBe(40000.0);
});

it('mengekspor csv mutasi dan menolak export tanpa nasabah', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'kolektor' => $kolektor, 'produk' => $produk] = seedLaporanMutasiPenarikan();

    $this->actingAs($admin);

    $kosong = Livewire::test(Laporan::class)->call('pilihSeksi', 'mutasi');
    $kosong->call('exportCsv')->assertStatus(200);
    expect($kosong->errors())->toHaveKeys(['mutasiNasabahId'])
        ->and(data_get($kosong->effects, 'download.content'))->toBeNull();

    SaldoProduk::create(['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id, 'saldo' => 50000]);
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    $tes = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'mutasi')
        ->set('mutasiNasabahId', $nasabah->id);
    $tes->call('exportCsv')->assertFileDownloaded(
        'laporan-mutasi-harian-'.now()->toDateString().'.csv'
    );

    $csv = base64_decode((string) data_get($tes->effects, 'download.content'));
    expect($csv)->toContain('Saldo Awal')
        ->and($csv)->toContain($nasabah->name)
        ->and($csv)->toContain('Setoran');
});

it('merangkum penarikan per status dengan lokasi waktu proses dan alasan batal', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanMutasiPenarikan();

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 10000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 500,
        'nominal_diterima' => 9500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
        'mempengaruhi_kas' => true,
    ]);
    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 20000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1000,
        'nominal_diterima' => 19000,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'selesai',
        'waktu_approval' => now()->setTime(9, 30, 0),
        'mempengaruhi_kas' => true,
    ]);
    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 1500,
        'nominal_diterima' => 28500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'dibatalkan',
        'alasan_batal' => 'berubah rencana',
        'mempengaruhi_kas' => false,
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDay()->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('pilihSeksi', 'penarikan')
        ->assertSee('Definisi angka')
        ->assertSee('Waktu Proses')
        ->assertSee('berubah rencana');

    $rows = collect($component->viewData('penarikanRows'));
    expect($rows)->toHaveCount(3);

    $dibatalkan = $rows->firstWhere('status', 'dibatalkan');
    expect($dibatalkan)->not->toBeNull()
        ->and($dibatalkan['alasan'])->toBe('berubah rencana')
        ->and($dibatalkan['lokasi'])->toBe('Rumah Kolektor')
        ->and($dibatalkan['waktu_proses'])->toBeNull();

    $rekap = collect($component->viewData('penarikanRekap'));
    expect($rekap)->toHaveCount(6)
        ->and($rekap->firstWhere('status', 'pending')['jumlah'])->toBe(1)
        ->and($rekap->firstWhere('status', 'selesai')['total'])->toBe(20000.0)
        ->and($rekap->firstWhere('status', 'dibatalkan')['jumlah'])->toBe(1);
});

it('mengekspor csv laporan penarikan', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanMutasiPenarikan();

    TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 15000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 750,
        'nominal_diterima' => 14250,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'dibatalkan',
        'alasan_batal' => 'dana belum siap',
        'mempengaruhi_kas' => false,
    ]);

    $this->actingAs($admin);
    $tes = Livewire::test(Laporan::class)
        ->set('periode', 'rentang')
        ->set('dariTanggal', now()->subDay()->toDateString())
        ->set('sampaiTanggal', now()->toDateString())
        ->call('pilihSeksi', 'penarikan');

    $tes->call('exportCsv')->assertFileDownloaded(
        'laporan-penarikan-rentang-'.now()->subDay()->toDateString().'-'.now()->toDateString().'.csv'
    );

    $csv = base64_decode((string) data_get($tes->effects, 'download.content'));
    expect($csv)->toContain('Lokasi')
        ->and($csv)->toContain('Alasan')
        ->and($csv)->toContain('dana belum siap');
});
