<?php

use App\Livewire\Admin\Laporan;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * @return array{
 *     admin: User,
 *     kolektorA: User,
 *     kolektorB: User,
 *     nasabah: User,
 *     produkBebas: ProdukTabungan,
 *     paket: ProdukTabungan,
 *     paketKosong: ProdukTabungan
 * }
 */
function p62Fixture(): array
{
    $admin = User::factory()->admin()->create();
    $kolektorA = User::factory()->kolektor()->create(['name' => 'Kolektor A']);
    $kolektorB = User::factory()->kolektor()->create(['name' => 'Kolektor B']);
    $nasabah = User::factory()->nasabah()->create();

    $produkBebas = ProdukTabungan::create([
        'nama' => 'Bebas P62',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 1000,
        'status' => 'aktif',
    ]);

    $paket = ProdukTabungan::create([
        'nama' => 'Paket P62',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
        'isi_paket' => [
            ['nama' => 'Beras', 'jumlah' => '1 karung'],
            ['nama' => 'Gula', 'jumlah' => 'satu karung'],
            ['nama' => 'Minyak', 'jumlah' => 2],
        ],
    ]);

    $paketKosong = ProdukTabungan::create([
        'nama' => 'Paket Kosong',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'harga_per_hari' => 5000,
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektorA', 'kolektorB', 'nasabah', 'produkBebas', 'paket', 'paketKosong');
}

function p62Setoran(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal, string $tanggal, string $status = 'tercatat'): TransaksiSetoran
{
    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'susulan',
        'status' => $status,
    ]);
}

/**
 * @param  array{terkumpul?: float, tunggakan?: float, keputusan_akhir?: string, serah?: string}  $opsi
 */
function p62Kepesertaan(ProdukTabungan $produk, User $nasabah, array $opsi = []): KepesertaanPaket
{
    return KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'total_aktual_terkumpul' => $opsi['terkumpul'] ?? 0,
        'tunggakan' => $opsi['tunggakan'] ?? 0,
        'keputusan_akhir' => $opsi['keputusan_akhir'] ?? null,
        'status_serah_terima' => $opsi['serah'] ?? 'belum',
    ]);
}

/**
 * @return Collection<int, array{nama: string, total_setoran: float, jumlah_transaksi: int, selisih: float}>
 */
function p62BarisKolektor(Testable $tes): Collection
{
    return collect($tes->viewData('kolektorRows'));
}

it('merangkum setoran dan selisih rekon per kolektor sesuai periode', function () {
    ['admin' => $admin, 'kolektorA' => $kolektorA, 'kolektorB' => $kolektorB, 'nasabah' => $nasabah, 'produkBebas' => $produk] = p62Fixture();

    p62Setoran($kolektorA, $nasabah, $produk, 50000, now()->toDateString());
    p62Setoran($kolektorA, $nasabah, $produk, 30000, now()->toDateString());
    p62Setoran($kolektorA, $nasabah, $produk, 40000, now()->subDay()->toDateString());
    p62Setoran($kolektorA, $nasabah, $produk, 99999, now()->toDateString(), 'dibatalkan');
    p62Setoran($kolektorB, $nasabah, $produk, 25000, now()->toDateString());

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'total_diterima' => 110000,
        'selisih' => 10000,
        'status' => 'lebih',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorB->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 100000,
        'total_diterima' => 95000,
        'selisih' => -5000,
        'status' => 'kurang',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->subDay()->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 149999,
        'selisih' => 99999,
        'status' => 'lebih',
    ]);

    $this->actingAs($admin);
    $rows = p62BarisKolektor(Livewire::test(Laporan::class)->call('pilihSeksi', 'kolektor'));

    $barisA = $rows->firstWhere('nama', 'Kolektor A');
    $barisB = $rows->firstWhere('nama', 'Kolektor B');

    expect(data_get($barisA, 'total_setoran'))->toBe(80000.0)
        ->and(data_get($barisA, 'jumlah_transaksi'))->toBe(2)
        ->and(data_get($barisA, 'selisih'))->toBe(10000.0)
        ->and(data_get($barisB, 'total_setoran'))->toBe(25000.0)
        ->and(data_get($barisB, 'jumlah_transaksi'))->toBe(1)
        ->and(data_get($barisB, 'selisih'))->toBe(-5000.0);
});

it('merangkum peserta aktif, terkumpul, dan tunggakan per paket', function () {
    ['admin' => $admin, 'paket' => $paket, 'paketKosong' => $paketKosong] = p62Fixture();

    foreach (range(1, 3) as $urut) {
        p62Kepesertaan($paket, User::factory()->nasabah()->create(), [
            'terkumpul' => [30000, 20000, 10000][$urut - 1],
            'tunggakan' => [1000, 0, 0][$urut - 1],
        ]);
    }
    p62Kepesertaan($paket, User::factory()->nasabah()->create(), [
        'terkumpul' => 500000,
        'tunggakan' => 90000,
        'keputusan_akhir' => 'lanjut',
    ]);
    p62Kepesertaan($paket, User::factory()->nasabah()->create(), [
        'terkumpul' => 700000,
        'tunggakan' => 80000,
        'serah' => 'sudah_diterima',
    ]);

    $this->actingAs($admin);
    $rows = collect(Livewire::test(Laporan::class)->call('pilihSeksi', 'paket')->viewData('paketRows'));
    $barisPaket = $rows->firstWhere('produk', 'Paket P62');

    expect($rows)->toHaveCount(2)
        ->and($rows->firstWhere('produk', 'Bebas P62'))->toBeNull()
        ->and(data_get($barisPaket, 'peserta_aktif'))->toBe(3)
        ->and(data_get($barisPaket, 'total_terkumpul'))->toBe(60000.0)
        ->and(data_get($barisPaket, 'total_tunggakan'))->toBe(1000.0)
        ->and(data_get($rows->firstWhere('produk', 'Paket Kosong'), 'peserta_aktif'))->toBe(0);
});

it('menghitung kebutuhan barang dari angka di awal teks isi paket', function () {
    ['admin' => $admin, 'paket' => $paket] = p62Fixture();

    foreach (range(1, 3) as $urut) {
        p62Kepesertaan($paket, User::factory()->nasabah()->create());
    }

    $this->actingAs($admin);
    $rows = collect(Livewire::test(Laporan::class)->call('pilihSeksi', 'barang')->viewData('barangRows'));

    expect($rows)->toHaveCount(3)
        ->and(data_get($rows->firstWhere('item', 'Beras'), 'total'))->toBe('3 karung')
        ->and(data_get($rows->firstWhere('item', 'Minyak'), 'total'))->toBe('6')
        ->and(data_get($rows->firstWhere('item', 'Beras'), 'peserta'))->toBe(3);
});

it('menampilkan ekspresi peserta kali teks asli bila jumlah tidak terparse', function () {
    ['admin' => $admin, 'paket' => $paket] = p62Fixture();

    foreach (range(1, 2) as $urut) {
        p62Kepesertaan($paket, User::factory()->nasabah()->create());
    }

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'barang')
        ->assertSee('2 peserta × satu karung');
});

it('mengekspor csv laporan per kolektor dengan sanitasi formula', function () {
    ['admin' => $admin, 'kolektorA' => $kolektorA, 'nasabah' => $nasabah, 'produkBebas' => $produk] = p62Fixture();

    $kolektorA->update(['name' => '=1+2']);
    p62Setoran($kolektorA, $nasabah, $produk, 50000, now()->toDateString());
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 60000,
        'selisih' => 10000,
        'status' => 'lebih',
    ]);

    $this->actingAs($admin);
    $tes = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'kolektor')
        ->call('exportCsv');

    $content = base64_decode((string) data_get($tes->effects, 'download.content'));

    expect($content)->toContain("'=1+2")
        ->and($content)->not->toContain("\n=1+2");
    $tes->assertFileDownloaded('laporan-kolektor-harian-'.now()->toDateString().'.csv');
});

it('mengekspor csv per paket dan kebutuhan barang', function () {
    ['admin' => $admin, 'paket' => $paket] = p62Fixture();
    p62Kepesertaan($paket, User::factory()->nasabah()->create(), ['terkumpul' => 30000]);

    $this->actingAs($admin);
    $tes = Livewire::test(Laporan::class)->call('pilihSeksi', 'paket')->call('exportCsv');

    $contentPaket = base64_decode((string) data_get($tes->effects, 'download.content'));
    $tes->assertFileDownloaded('laporan-paket-'.now()->toDateString().'.csv');
    expect($contentPaket)->toContain('Paket P62')->toContain('30000');

    $tes->call('pilihSeksi', 'barang')->call('exportCsv');
    $contentBarang = base64_decode((string) data_get($tes->effects, 'download.content'));
    $tes->assertFileDownloaded('laporan-kebutuhan-barang-'.now()->toDateString().'.csv');
    expect($contentBarang)->toContain('Beras')->toContain('1 karung');
});

it('menampilkan tab seksi laporan dan tombol cetak', function () {
    ['admin' => $admin] = p62Fixture();

    $this->actingAs($admin);
    Livewire::test(Laporan::class)
        ->assertSee('Per Kolektor')
        ->assertSee('Per Paket')
        ->assertSee('Kebutuhan Barang')
        ->assertSee('Cetak')
        ->assertSee('window.print', false);
});

it('mengabaikan seksi laporan yang tidak dikenal', function () {
    ['admin' => $admin] = p62Fixture();

    $this->actingAs($admin);
    $tes = Livewire::test(Laporan::class)->call('pilihSeksi', 'tidak-ada');

    expect($tes->get('seksi'))->toBe('keuangan');
});
