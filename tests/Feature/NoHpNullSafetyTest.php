<?php

use App\Livewire\Nasabah\Pengaturan;
use App\Models\KolektorNasabah;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

/**
 * Fixtures P2.1: nasabah offline (tanpa HP) + halaman yang wajib tetap render.
 *
 * @return array{nasabah: User, kolektor: User, produk: ProdukTabungan, setoran: TransaksiSetoran}
 */
function offlineRenderFixture(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['no_hp' => null]);

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas Offline',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $nasabah->nasabahProfil()->create([
        'nama' => $nasabah->name,
        'alamat' => 'Jl. Offline Tanpa HP No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $setoran = TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 50000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    return compact('nasabah', 'kolektor', 'produk', 'setoran');
}

it('membuat dua nasabah offline dengan no_hp null tanpa bentrok unique', function () {
    $a = User::create([
        'name' => 'Offline Satu',
        'no_hp' => null,
        'pin_hash' => 'x',
        'role' => 'nasabah',
        'mode_akses' => 'offline',
    ]);
    $b = User::create([
        'name' => 'Offline Dua',
        'no_hp' => null,
        'pin_hash' => 'y',
        'role' => 'nasabah',
        'mode_akses' => 'offline',
    ]);

    $digital = User::create([
        'name' => 'Digital Default',
        'no_hp' => '081299900001',
        'pin_hash' => 'z',
        'role' => 'nasabah',
    ]);

    expect($a->fresh()->no_hp)->toBeNull()
        ->and($b->fresh()->no_hp)->toBeNull()
        ->and($a->fresh()->mode_akses)->toBe('offline')
        ->and($digital->fresh()->mode_akses)->toBe('digital');
});

it('menyimpan penarikan jalur offline_kolektor dan metode tanpa_verifikasi_offline', function () {
    ['nasabah' => $nasabah, 'produk' => $produk] = offlineRenderFixture();

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 30000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 30000,
        'jalur_pengajuan' => 'offline_kolektor',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'metode_verifikasi' => 'tanpa_verifikasi_offline',
        'status' => 'selesai',
    ]);

    expect($penarikan->fresh()->jalur_pengajuan)->toBe('offline_kolektor')
        ->and($penarikan->fresh()->metode_verifikasi)->toBe('tanpa_verifikasi_offline');
});

it('halaman daftar nasabah admin dan kolektor render tanpa error saat no_hp null', function () {
    $admin = User::factory()->admin()->create();
    ['kolektor' => $kolektor, 'nasabah' => $nasabah] = offlineRenderFixture();

    $this->actingAs($admin)
        ->get(route('admin.nasabah.index'))
        ->assertOk();

    $this->actingAs($kolektor)
        ->get(route('kolektor.daftar-nasabah.index'))
        ->assertOk();

    $this->actingAs($kolektor)
        ->get(route('kolektor.nasabah.index'))
        ->assertOk();
});

it('halaman detail nasabah dan struk setoran render tanpa error saat no_hp null', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'setoran' => $setoran] = offlineRenderFixture();

    $this->actingAs($admin)
        ->get(route('admin.nasabah.detail', $nasabah))
        ->assertOk();

    $this->actingAs($admin)
        ->get(route('struk.setoran', $setoran))
        ->assertOk();
});

it('daftar nasabah admin menampilkan label offline saat no_hp kosong', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah] = offlineRenderFixture();

    $this->actingAs($admin)
        ->get(route('admin.nasabah.index'))
        ->assertOk()
        ->assertSee('(offline)');
});

it('perintah normalisasi nomor hp melewati pengguna tanpa no_hp', function () {
    User::factory()->nasabah()->create(['no_hp' => null]);

    $this->artisan('users:normalisasi-hp')
        ->assertSuccessful();
});

it('registrasi halaman dan pengaturan profil render saat no_hp null', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah] = offlineRenderFixture();

    $this->actingAs($admin)
        ->get(route('admin.registrasi.index'))
        ->assertOk();

    Livewire::actingAs($nasabah)
        ->test(Pengaturan::class);
});
