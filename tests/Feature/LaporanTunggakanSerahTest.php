<?php

use App\Livewire\Admin\Laporan;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, nasabah: User, produk: ProdukTabungan}
 */
function seedLaporanTunggakanSerah(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = ProdukTabungan::create([
        'nama' => 'Paket Sehat',
        'tipe' => 'paket',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'harga_per_hari' => 10000,
        'batas_toleransi_tunggakan_hari' => 3,
        'status' => 'aktif',
    ]);

    return compact('admin', 'nasabah', 'produk');
}

it('menampilkan laporan tunggakan nasabah dengan hari rupiah status alert dan keputusan', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanTunggakanSerah();

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'tunggakan' => 5,
        'status_alert' => 'peringatan',
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDay()->toDateString(),
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'tunggakan')
        ->assertSee('Definisi angka')
        ->assertSee('Status Alert')
        ->assertSee('Peringatan');

    $rows = collect($component->viewData('tunggakanRows'));
    expect($rows)->toHaveCount(1);
    $baris = $rows->first();
    expect($baris['nasabah'])->toBe($nasabah->name)
        ->and($baris['tunggakan_hari'])->toBe(5)
        ->and($baris['tunggakan_rupiah'])->toBe(50000.0)
        ->and($baris['status_alert'])->toBe('Peringatan')
        ->and($baris['keputusan_akhir'])->toBe('-');
});

it('menampilkan laporan serah terima paket dengan metode penerima tanggal dan foto bukti', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanTunggakanSerah();

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'status_serah_terima' => 'sudah_diterima',
        'metode_pengambilan' => 'ambil_sendiri',
        'diterima_oleh' => 'Budi',
        'tanggal_serah_terima' => now()->toDateString(),
        'bukti_foto_url' => 'https://contoh.test/bukti-1.jpg',
    ]);
    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(30)->toDateString(),
        'status_serah_terima' => 'belum',
    ]);

    $this->actingAs($admin);
    $component = Livewire::test(Laporan::class)
        ->call('pilihSeksi', 'serah')
        ->assertSee('Definisi angka')
        ->assertSee('Foto Bukti')
        ->assertSee('Budi')
        ->assertSee('https://contoh.test/bukti-1.jpg');

    $rows = collect($component->viewData('serahRows'));
    expect($rows)->toHaveCount(2);

    $sudah = $rows->firstWhere('status_kunci', 'sudah_diterima');
    expect($sudah)->not->toBeNull()
        ->and($sudah['penerima'])->toBe('Budi')
        ->and($sudah['metode'])->toBe('Ambil Sendiri')
        ->and($sudah['foto'])->toBe('https://contoh.test/bukti-1.jpg')
        ->and($sudah['tanggal'])->toBe(now()->format('d/m/Y'));

    $belum = $rows->firstWhere('status_kunci', 'belum');
    expect($belum)->not->toBeNull()
        ->and($belum['penerima'])->toBe('-')
        ->and($belum['foto'])->toBeNull()
        ->and($belum['tanggal'])->toBeNull();
});

it('mengekspor csv laporan tunggakan dan serah terima', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk] = seedLaporanTunggakanSerah();

    KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'tunggakan' => 4,
        'status_alert' => 'perlu_review',
        'keputusan_akhir' => 'gagal_dikembalikan',
        'status_serah_terima' => 'sudah_diterima',
        'metode_pengambilan' => 'diantar_kolektor',
        'diterima_oleh' => 'Siti',
        'tanggal_serah_terima' => now()->subDay()->toDateString(),
        'bukti_foto_url' => 'https://contoh.test/bukti-2.jpg',
    ]);

    $this->actingAs($admin);

    $tunggakan = Livewire::test(Laporan::class)->call('pilihSeksi', 'tunggakan');
    $tunggakan->call('exportCsv')->assertFileDownloaded('laporan-tunggakan-'.now()->toDateString().'.csv');
    $csvTunggakan = base64_decode((string) data_get($tunggakan->effects, 'download.content'));
    expect($csvTunggakan)->toContain('Tunggakan (Rp)')
        ->and($csvTunggakan)->toContain('Gagal Dikembalikan')
        ->and($csvTunggakan)->toContain('40000');

    $serah = Livewire::test(Laporan::class)->call('pilihSeksi', 'serah');
    $serah->call('exportCsv')->assertFileDownloaded('laporan-serah-terima-'.now()->toDateString().'.csv');
    $csvSerah = base64_decode((string) data_get($serah->effects, 'download.content'));
    expect($csvSerah)->toContain('Penerima')
        ->and($csvSerah)->toContain('Diantar Kolektor')
        ->and($csvSerah)->toContain('Siti');
});
