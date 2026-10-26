<?php

use App\Livewire\Admin\Komisi;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

/**
 * @return array{admin: User, kolektor: User, produk: ProdukTabungan, lain: ProdukTabungan}
 */
function siapkanKomisiEkspor(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $nasabah->update(['name' => 'Nasabah Test']);

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $lain = ProdukTabungan::create([
        'nama' => 'Tabungan Berkala',
        'tipe' => 'paket',
        'persen_komisi' => 3.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $buat = fn (array $extra): TransaksiPenarikan => TransaksiPenarikan::create(array_merge([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
        'disetujui_oleh' => $admin->id,
        'waktu_approval' => now(),
    ], $extra));

    // P1: approved, pencairan masih bulan ini, dibayar kolektor.
    $buat([
        'waktu_approval' => now()->subDays(2),
        'waktu_pencairan' => now()->subDay(),
        'dibayar_oleh' => $kolektor->id,
    ]);

    // P2: selesai, lokasi rumah kolektor.
    $buat([
        'nominal_diminta' => 100000,
        'nominal_komisi' => 4000,
        'nominal_diterima' => 96000,
        'status' => 'selesai',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'waktu_approval' => now()->subDay(),
        'waktu_pencairan' => now(),
        'dibayar_oleh' => $kolektor->id,
    ]);

    // P3: pending, tidak ikut hitungan.
    $buat([
        'nominal_komisi' => 1500,
        'status' => 'pending',
        'waktu_approval' => now(),
    ]);

    // P4: approved tapi pencairan bulan lalu (untuk uji dasar tanggal).
    $buat([
        'nominal_diminta' => 40000,
        'nominal_komisi' => 1000,
        'nominal_diterima' => 39000,
        'waktu_approval' => now(),
        'waktu_pencairan' => now()->subMonth(),
    ]);

    return ['admin' => $admin, 'kolektor' => $kolektor, 'produk' => $produk, 'lain' => $lain];
}

function csvKomisi(object $tes): string
{
    $tes->call('exportCsv');

    return base64_decode((string) data_get($tes->effects, 'download.content'));
}

it('mengekspor csv komisi dengan filter yang sama seperti tampilan', function () {
    siapkanKomisiEkspor();
    $this->actingAs(User::factory()->admin()->create());

    $tes = Livewire::test(Komisi::class);

    expect($tes->get('totalKomisi'))->toBe(7500.0)
        ->and($tes->get('totalTransaksi'))->toBe(3);

    $csv = csvKomisi($tes);
    $barisCsv = explode("\n", trim(substr($csv, 3)));

    expect($csv)->toStartWith("\xEF\xBB\xBF")
        ->and(str_getcsv($barisCsv[0]))->toBe([
            'Tanggal Approval',
            'Tanggal Pencairan',
            'Nasabah',
            'Produk',
            'Jalur',
            'Lokasi Pengambilan',
            'Kolektor Pembayar',
            'Nominal Diminta',
            'Persen Komisi',
            'Nominal Komisi',
            'Nominal Diterima',
            'Status',
        ])
        ->and($csv)->toContain('Nasabah Test')
        ->and($csv)->toContain('Tabungan Bebas')
        ->and($csv)->toContain('190000.00')
        ->and($csv)->toContain('7500.00')
        ->and($csv)->toContain('182500.00')
        ->and($csv)->toContain('TOTAL')
        ->and($barisCsv)->toHaveCount(5);

    // Filter layar = filter ekspor: produk lain mengosongkan kedua-duanya.
    $produkLain = ProdukTabungan::where('nama', 'Tabungan Berkala')->firstOrFail();
    $tes->set('produkId', (string) $produkLain->id);

    expect($tes->get('totalKomisi'))->toBe(0.0)
        ->and($tes->get('totalTransaksi'))->toBe(0);

    $csvKosong = csvKomisi($tes);
    expect($csvKosong)->not->toContain('Nasabah Test')
        ->and(substr_count($csvKosong, "\n"))->toBe(2);

    $tes->assertFileDownloaded(
        'komisi-'.now()->startOfMonth()->toDateString().'-'.now()->toDateString().'.csv'
    );
});

it('menerapkan filter status lokasi kolektor dan dasar tanggal pada ekspor', function () {
    $data = siapkanKomisiEkspor();
    $this->actingAs($data['admin']);

    $tes = Livewire::test(Komisi::class);

    $tes->set('status', 'selesai');
    expect(substr_count(csvKomisi($tes), "\n"))->toBe(3)
        ->and($tes->get('totalKomisi'))->toBe(4000.0);

    $tes->set('status', 'approved');
    expect(substr_count(csvKomisi($tes), "\n"))->toBe(4)
        ->and($tes->get('totalKomisi'))->toBe(3500.0);

    $tes->set('status', '');
    $tes->set('lokasiPengambilan', 'rumah_kolektor');
    expect(substr_count(csvKomisi($tes), "\n"))->toBe(3)
        ->and($tes->get('totalTransaksi'))->toBe(1);

    $tes->set('lokasiPengambilan', '');
    $tes->set('kolektorId', (string) $data['kolektor']->id);
    expect(substr_count(csvKomisi($tes), "\n"))->toBe(4)
        ->and($tes->get('totalTransaksi'))->toBe(2);

    $tes->set('kolektorId', '');
    $tes->set('dasarTanggal', 'waktu_pencairan');
    expect(substr_count(csvKomisi($tes), "\n"))->toBe(4)
        ->and($tes->get('totalKomisi'))->toBe(6500.0)
        ->and($tes->get('totalTransaksi'))->toBe(2);
});

it('mengamankan sel teks formula saat ekspor komisi', function () {
    $data = siapkanKomisiEkspor();
    $nasabah = User::where('role', 'nasabah')->firstOrFail();
    $nasabah->update(['name' => '=HYPERLINK("http://evil.example","klik")']);
    $this->actingAs($data['admin']);

    $csv = csvKomisi(Livewire::test(Komisi::class));

    expect($csv)->toContain("'=HYPERLINK")
        ->and($csv)->not->toContain("\n=HYPERLINK");
});

it('mencatat log ekspor_komisi dengan filter dan jumlah baris', function () {
    siapkanKomisiEkspor();
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $tes = Livewire::test(Komisi::class);
    $tes->set('status', 'approved');
    csvKomisi($tes);

    $log = LogAktivitas::where('aksi', 'ekspor_komisi')->first();

    expect($log)->not->toBeNull()
        ->and($log->user_id)->toBe($admin->id)
        ->and($log->detail['jumlah_baris'])->toBe(2)
        ->and($log->detail['status'])->toBe('approved')
        ->and($log->detail['dari'])->toBe(now()->startOfMonth()->toDateString());
});

it('menampilkan tombol cetak dan unduh csv', function () {
    siapkanKomisiEkspor();
    $this->actingAs(User::factory()->admin()->create());

    Livewire::test(Komisi::class)
        ->assertSee('Unduh CSV')
        ->assertSee('Cetak')
        ->assertSee('window.print', false)
        ->assertSee('laporan-cetak', false);
});

it('menampilkan rekap komisi per bulan', function () {
    siapkanKomisiEkspor();
    $this->actingAs(User::factory()->admin()->create());

    $namaBulan = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    $labelBulan = $namaBulan[(int) now()->format('m') - 1].' '.now()->format('Y');

    Livewire::test(Komisi::class)
        ->assertSee('Komisi per Bulan')
        ->assertSee($labelBulan)
        ->assertSet('rekapBulan.0.total_komisi', 7500.0);
});

it('menolak non-admin dari halaman dan ekspor komisi', function () {
    siapkanKomisiEkspor();
    $nasabah = User::factory()->nasabah()->create();

    Livewire::actingAs($nasabah)->test(Komisi::class)->assertForbidden();
});
