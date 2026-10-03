<?php

use App\Livewire\Admin\KasKolektor;
use App\Livewire\Admin\Pengaturan;
use App\Models\AdminSetting;
use App\Models\ProdukTabungan;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function setorKasP34(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal, string $tanggal, bool $sudahDisetor = false): TransaksiSetoran
{
    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => $tanggal,
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
        'sudah_disetor_ke_kantor' => $sudahDisetor,
    ]);
}

function seedKasP34(): array
{
    $admin = User::factory()->admin()->create();
    $kolektorA = User::factory()->kolektor()->create();
    $kolektorB = User::factory()->kolektor()->create();
    $kolektorC = User::factory()->kolektor()->create();
    $kolektorNonaktif = User::factory()->kolektor()->create(['status_akun' => 'terkunci']);
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Produk Kas P34',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    setorKasP34($kolektorA, $nasabah, $produk, 50000, now()->subDays(2)->toDateString());
    setorKasP34($kolektorA, $nasabah, $produk, 30000, now()->toDateString());
    setorKasP34($kolektorB, $nasabah, $produk, 10000, now()->toDateString(), sudahDisetor: true);
    setorKasP34($kolektorC, $nasabah, $produk, 40000, now()->subDays(10)->toDateString());
    setorKasP34($kolektorNonaktif, $nasabah, $produk, 70000, now()->toDateString());

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 0,
        'status' => 'pending',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 0,
        'status' => 'pending',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorC->id,
        'tanggal_setor' => now()->toDateString(),
        'total_seharusnya' => 0,
        'status' => 'pending',
    ]);

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->subDays(3)->toDateString(),
        'total_seharusnya' => 110000,
        'total_diterima' => 100000,
        'selisih' => -10000,
        'status' => 'kurang',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektorA->id,
        'tanggal_setor' => now()->subDays(2)->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 55000,
        'selisih' => 5000,
        'status' => 'lebih',
    ]);

    return compact('admin', 'kolektorA', 'kolektorB', 'kolektorC', 'kolektorNonaktif', 'nasabah', 'produk');
}

function barisKasP34(iterable $rows, User $kolektor): ?array
{
    foreach ($rows as $row) {
        if ($row['kolektor_id'] === $kolektor->id) {
            return $row;
        }
    }

    return null;
}

it('menampilkan angka kas per kolektor sesuai fixture', function () {
    ['admin' => $admin, 'kolektorA' => $a, 'kolektorB' => $b, 'kolektorC' => $c, 'kolektorNonaktif' => $nonaktif] = seedKasP34();

    $this->actingAs($admin);
    $component = Livewire::test(KasKolektor::class);
    $rows = collect($component->viewData('daftarKas'));

    $rowA = barisKasP34($rows, $a);
    expect($rowA['total_belum_disetor'])->toBe(80000.0)
        ->and($rowA['jumlah_transaksi'])->toBe(2)
        ->and($rowA['umur_terlama_hari'])->toBe(2)
        ->and($rowA['pengajuan_pending'])->toBe(2)
        ->and($rowA['selisih_kumulatif'])->toBe(-5000.0)
        ->and($rowA['lewat_batas'])->toBeFalse();

    $rowB = barisKasP34($rows, $b);
    expect($rowB['total_belum_disetor'])->toBe(0.0)
        ->and($rowB['jumlah_transaksi'])->toBe(0)
        ->and($rowB['umur_terlama_hari'])->toBe(0);

    $rowC = barisKasP34($rows, $c);
    expect($rowC['total_belum_disetor'])->toBe(40000.0)
        ->and($rowC['jumlah_transaksi'])->toBe(1)
        ->and($rowC['umur_terlama_hari'])->toBe(10)
        ->and($rowC['pengajuan_pending'])->toBe(1)
        ->and($rowC['selisih_kumulatif'])->toBe(0.0);

    expect(barisKasP34($rows, $nonaktif))->toBeNull()
        ->and($rows)->toHaveCount(3);
});

it('menandai baris yang melewati batas kas', function () {
    ['admin' => $admin, 'kolektorA' => $a, 'kolektorC' => $c] = seedKasP34();

    AdminSetting::set('batas_kas_kolektor', '50000');

    $this->actingAs($admin);
    $rows = collect(Livewire::test(KasKolektor::class)->viewData('daftarKas'));

    expect(barisKasP34($rows, $a)['lewat_batas'])->toBeTrue()
        ->and(barisKasP34($rows, $c)['lewat_batas'])->toBeFalse();
});

it('menandai baris yang melewati batas hari', function () {
    ['admin' => $admin, 'kolektorA' => $a, 'kolektorC' => $c] = seedKasP34();

    AdminSetting::set('batas_hari_kas', '3');

    $this->actingAs($admin);
    $rows = collect(Livewire::test(KasKolektor::class)->viewData('daftarKas'));

    expect(barisKasP34($rows, $c)['lewat_batas'])->toBeTrue()
        ->and(barisKasP34($rows, $a)['lewat_batas'])->toBeFalse();
});

it('kartu dashboard menampilkan total kas dan jumlah kolektor melewati batas', function () {
    ['admin' => $admin] = seedKasP34();

    expect(KasKolektor::ringkasUntukDashboard())
        ->toBe(['total_kas' => 120000.0, 'lewat_batas' => 0]);

    AdminSetting::set('batas_kas_kolektor', '50000');

    expect(KasKolektor::ringkasUntukDashboard())
        ->toBe(['total_kas' => 120000.0, 'lewat_batas' => 1]);

    $response = $this->actingAs($admin)->get('/dashboard');
    $response->assertOk()
        ->assertViewIs('dashboard');
    expect((float) $response->viewData('totalKasKolektor'))->toBe(120000.0)
        ->and($response->viewData('kolektorLewatBatas'))->toBe(1);
});

it('hanya admin yang bisa mengakses halaman kas kolektor', function () {
    ['kolektorA' => $kolektor, 'nasabah' => $nasabah] = seedKasP34();

    $this->actingAs($nasabah)->get('/admin/kas-kolektor')->assertForbidden();
    $this->actingAs($kolektor)->get('/admin/kas-kolektor')->assertForbidden();
});

it('menyimpan batas kas kolektor dari pengaturan', function () {
    ['admin' => $admin] = seedKasP34();

    $this->actingAs($admin);
    Livewire::test(Pengaturan::class)
        ->set('batasKasKolektor', '150000')
        ->set('batasHariKas', '7')
        ->call('simpanKonfigurasi');

    expect((string) AdminSetting::get('batas_kas_kolektor'))->toBe('150000')
        ->and((string) AdminSetting::get('batas_hari_kas'))->toBe('7');
});
