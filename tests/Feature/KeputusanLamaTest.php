<?php

use App\Livewire\Admin\DetailNasabah;
use App\Livewire\Admin\Laporan;
use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\TinjauanPenarikanOffline;
use App\Livewire\Kolektor\Pengaturan as KolektorPengaturan;
use App\Livewire\Nasabah\Pengaturan as NasabahPengaturan;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * Batch sepuluh keputusan lama yang dijawab pengguna: kolom uang rata kanan,
 * baris tabel 44px, permukaan gelap #171717 lewat dark:bg-surface, keluar
 * akun netral, baris versi pengaturan, frasa sesi berakhir D26, dan chip
 * peran topbar tidak ganda. Bottom nav admin, landing uppercase, serta ubin
 * lokasi penarikan-offline hijau dipertahankan tanpa perubahan.
 *
 * @return array{0: User, 1: User}
 */
function keputusanLamaDetailNasabah(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Keputusan',
        'alamat' => 'Jl. Keputusan No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Keputusan',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 120000,
    ]);

    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->subDays(5)->toDateString(),
        'tanggal_input_sistem' => now()->subDays(5),
        'input_by' => $admin->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    return [$admin, $nasabah];
}

it('laporan memakai baris 44px dan meratakan kolom uang ke kanan', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $keuangan = Livewire::test(Laporan::class)
        ->set('seksi', 'keuangan')
        ->set('periode', 'bulanan')
        ->html();

    expect($keuangan)
        ->not->toContain('px-3 py-2', false)
        ->toContain('<th class="px-4 py-3 font-mono text-xs uppercase tracking-wider text-mute text-right">Setoran</th>', false);

    $mutasi = Livewire::test(Laporan::class)->set('seksi', 'mutasi')->html();

    expect($mutasi)
        ->toContain('text-mute text-right">Nominal</th>', false)
        ->not->toContain('px-3 py-2', false);

    $sumber = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/admin/laporan.blade.php');

    expect($sumber)
        ->not->toContain('px-3 py-2', false)
        ->toContain('tabular-nums text-right text-sm text-ink">Rp ', false)
        ->toContain('tabular-nums text-right text-sm {{ $baris[\'selisih\'] < 0', false);
});

it('detail-nasabah, monitoring, dan tinjauan meratakan kolom nominal', function () {
    [$admin, $nasabah] = keputusanLamaDetailNasabah();
    $this->actingAs($admin);

    $detail = Livewire::test(DetailNasabah::class, ['user' => $nasabah])->html();

    expect($detail)
        ->toContain('<th class="px-5 py-3 font-medium text-right">Nominal</th>', false)
        ->toContain('whitespace-nowrap px-5 py-3 tabular-nums text-right text-sm">Rp ', false);

    $monitoring = Livewire::test(MonitoringSetoran::class)->html();

    expect($monitoring)
        ->toContain('text-mute text-right">Nominal</th>', false);

    $tinjauan = Livewire::test(TinjauanPenarikanOffline::class)->html();

    expect($tinjauan)
        ->toContain('text-mute text-right">Nominal</th>', false);

    $sumberMonitoring = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/admin/monitoring-setoran.blade.php');
    $sumberTinjauan = file_get_contents(dirname(__DIR__, 2).'/resources/views/livewire/admin/tinjauan-penarikan-offline.blade.php');

    expect($sumberMonitoring)
        ->toContain('text-right text-sm font-medium text-ink">Rp ', false)
        ->toContain('text-right text-xs text-mute">Asli: Rp ', false);
    expect($sumberTinjauan)
        ->toContain('tabular-nums text-right text-sm font-medium text-ink dark:text-white', false);
});

it('seluruh tampilan memakai dark:bg-surface, bukan dark:bg-zinc-900', function () {
    $akar = dirname(__DIR__, 2).'/resources/views';
    $penyimpangan = [];
    $memakaiSurface = 0;

    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($akar, FilesystemIterator::SKIP_DOTS));

    foreach ($rii as $berkas) {
        if (! $berkas->isFile() || ! str_ends_with($berkas->getFilename(), '.blade.php')) {
            continue;
        }

        $isi = file_get_contents($berkas->getPathname());

        if (str_contains($isi, 'dark:bg-zinc-900')) {
            $penyimpangan[] = str_replace($akar.'/', '', $berkas->getPathname());
        }

        if (str_contains($isi, 'dark:bg-surface')) {
            $memakaiSurface++;
        }
    }

    expect($penyimpangan)->toBe([])
        ->and($memakaiSurface)->toBeGreaterThan(0);
});

it('baris keluar akun di pengaturan kolektor memakai warna netral', function () {
    $kolektor = User::factory()->kolektor()->create();

    $html = Livewire::actingAs($kolektor)->test(KolektorPengaturan::class)->html();

    expect($html)
        ->toContain('text-sm font-medium text-ink">Keluar akun', false)
        ->not->toContain('text-sm font-medium text-danger">Keluar akun', false)
        ->not->toContain('rounded-full bg-danger-soft', false);
});

it('pengaturan ketiga portal menampilkan baris versi aplikasi', function () {
    $nasabah = User::factory()->nasabah()->create();

    expect(Livewire::actingAs($nasabah)->test(NasabahPengaturan::class)->html())
        ->toContain('Tabungan Digital · versi alpha', false);

    $kolektor = User::factory()->kolektor()->create();

    expect(Livewire::actingAs($kolektor)->test(KolektorPengaturan::class)->html())
        ->toContain('Tabungan Digital · versi alpha', false);

    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get('/admin/pengaturan')
        ->assertOk()
        ->assertSee('Tabungan Digital · versi alpha');
});

it('flash sesi berakhir memakai frasa D26 di kedua portal', function () {
    $kolektor = file_get_contents(dirname(__DIR__, 2).'/app/Livewire/Kolektor/PenarikanOffline.php');
    $admin = file_get_contents(dirname(__DIR__, 2).'/app/Livewire/Admin/TinjauanPenarikanOffline.php');

    foreach ([$kolektor, $admin] as $sumber) {
        expect($sumber)
            ->toContain('Sesi berakhir karena tidak aktif. Silakan masuk kembali.', false)
            ->not->toContain('Sesi berakhir. Silakan login kembali.', false);
    }
});

it('chip peran di topbar admin tidak ganda pada layar lebar', function () {
    $admin = User::factory()->admin()->create();

    $halaman = $this->actingAs($admin)->get('/admin/pengaturan')->assertOk();

    $halaman->assertSee('hidden sm:inline-flex lg:hidden');
    expect(substr_count($halaman->getContent(), 'data-test="chip-peran"'))->toBe(2);
});
