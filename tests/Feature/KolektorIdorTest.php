<?php

use App\Livewire\Admin\SerahTerimaPaket;
use App\Livewire\Kolektor\InputSetoran;
use App\Livewire\Kolektor\JadwalKunjungan;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Livewire\Kolektor\VerifikasiPenarikan;
use App\Models\KepesertaanPaket;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

it('rejects kolektor input setoran for nasabah not assigned to them via IDOR', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor2->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('selectedNasabah', null)
        ->assertSet('nasabahId', '');
});

it('allows kolektor input setoran for their own assigned nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(InputSetoran::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('nasabahId', (string) $nasabah->id)
        ->assertSet('selectedNasabah', fn ($val) => $val !== null);
});

it('rejects kolektor penarikan offline for nasabah not assigned to them via IDOR', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor2->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('selectedNasabah', null)
        ->assertSet('nasabahId', '');
});

it('allows kolektor penarikan offline for their own assigned nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Test',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->set('nasabahId', $nasabah->id)
        ->assertSet('selectedNasabah', fn ($val) => $val !== null);
});

function seedJadwalIdor(): array
{
    $kolektor = User::factory()->kolektor()->create();
    $kolektor2 = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Jadwal',
        'alamat' => 'Jl. Jadwal No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektor2->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektor2->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return [$kolektor, $kolektor2, $nasabah];
}

it('rejects kolektor update jadwal kunjungan for nasabah not assigned to them via IDOR', function () {
    [$kolektor, $kolektor2, $nasabah] = seedJadwalIdor();

    $this->actingAs($kolektor);

    Livewire::test(JadwalKunjungan::class)
        ->call('updateStatus', $nasabah->id, 'dikunjungi');

    $this->assertDatabaseMissing('jadwal_kunjungan', [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
    ]);
});

it('allows kolektor update jadwal kunjungan for their own assigned nasabah', function () {
    [$kolektor, $kolektor2, $nasabah] = seedJadwalIdor();

    KolektorNasabah::where('kolektor_id', $kolektor2->id)->update(['status' => 'nonaktif', 'aktif_unik' => null]);
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(JadwalKunjungan::class)
        ->call('updateStatus', $nasabah->id, 'dikunjungi');

    $this->assertDatabaseHas('jadwal_kunjungan', [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'status_kunjungan' => 'dikunjungi',
    ]);
});

it('rejects jadwal kunjungan update with invalid status', function () {
    [$kolektor, $kolektor2, $nasabah] = seedJadwalIdor();

    KolektorNasabah::where('kolektor_id', $kolektor2->id)->update(['status' => 'nonaktif', 'aktif_unik' => null]);
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(JadwalKunjungan::class)
        ->call('updateStatus', $nasabah->id, 'sembarangan');

    $this->assertDatabaseMissing('jadwal_kunjungan', [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
    ]);
});

it('rejects jadwal kunjungan update with invalid tanggal', function () {
    [$kolektor, $kolektor2, $nasabah] = seedJadwalIdor();

    KolektorNasabah::where('kolektor_id', $kolektor2->id)->update(['status' => 'nonaktif', 'aktif_unik' => null]);
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $this->actingAs($kolektor);

    Livewire::test(JadwalKunjungan::class)
        ->set('tanggal', 'bukan-tanggal')
        ->call('updateStatus', $nasabah->id, 'dikunjungi');

    $this->assertDatabaseMissing('jadwal_kunjungan', [
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
    ]);
});

it('prevents kolektor A from viewing or opening modal for penarikan of nasabah owned by kolektor B', function () {
    $kolektorA = User::factory()->kolektor()->create();
    $kolektorB = User::factory()->kolektor()->create();
    $nasabahB = User::factory()->nasabah()->create(['name' => 'Nasabah Rahasia B']);

    NasabahProfil::create([
        'user_id' => $nasabahB->id,
        'nama' => 'Nasabah Rahasia B',
        'alamat' => 'Jl. Rahasia No. 9',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $kolektorB->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektorB->id,
        'nasabah_id' => $nasabahB->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas Test IDOR',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    $penarikanB = TransaksiPenarikan::create([
        'nasabah_id' => $nasabahB->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 0,
        'nominal_komisi' => 0,
        'nominal_diterima' => 50000,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    // Kolektor A tidak boleh melihat data penarikan B
    $this->actingAs($kolektorA);

    Livewire::test(VerifikasiPenarikan::class)
        ->set('penarikanId', $penarikanB->id)
        ->assertViewHas('penarikanAktif', null)
        ->assertDontSee('Nasabah Rahasia B')
        ->assertDontSee('50.000');

    // Kolektor A membuka modal penarikan B harus ditolak 403
    Livewire::test(VerifikasiPenarikan::class)
        ->call('bukaModal', $penarikanB->id)
        ->assertForbidden();

    // Kasus normal: Kolektor B berwenang melihat dan membuka modal
    $this->actingAs($kolektorB);

    Livewire::test(VerifikasiPenarikan::class)
        ->call('bukaModal', $penarikanB->id)
        ->assertSet('penarikanId', $penarikanB->id)
        ->assertSet('showModal', true)
        ->assertViewHas('penarikanAktif', fn ($val) => $val !== null && $val->id === $penarikanB->id);
});

it('prevents kolektor A from viewing lastSetoran of kolektor B via lastSetoranId', function () {
    $kolektorA = User::factory()->kolektor()->create();
    $kolektorB = User::factory()->kolektor()->create();
    $nasabahB = User::factory()->nasabah()->create(['name' => 'Nasabah Setoran B']);

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas Test IDOR',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'status' => 'aktif',
    ]);

    $setoranB = TransaksiSetoran::create([
        'nasabah_id' => $nasabahB->id,
        'produk_id' => $produk->id,
        'nominal' => 75000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektorB->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);

    // Kolektor A mencoba melihat setoran milik Kolektor B
    $this->actingAs($kolektorA);

    Livewire::test(InputSetoran::class)
        ->set('lastSetoranId', $setoranB->id)
        ->assertViewHas('lastSetoran', null)
        ->assertDontSee('75.000');

    // Kolektor B bisa melihat setorannya sendiri
    $this->actingAs($kolektorB);

    Livewire::test(InputSetoran::class)
        ->set('lastSetoranId', $setoranB->id)
        ->assertViewHas('lastSetoran', fn ($val) => $val !== null && $val->id === $setoranB->id);
});

it('prevents kolektor A from viewing kepesertaan paket of nasabah owned by kolektor B in SerahTerimaPaket', function () {
    $kolektorA = User::factory()->kolektor()->create();
    $kolektorB = User::factory()->kolektor()->create();
    $admin = User::factory()->admin()->create();
    $nasabahB = User::factory()->nasabah()->create(['name' => 'Nasabah Paket B']);

    NasabahProfil::create([
        'user_id' => $nasabahB->id,
        'nama' => 'Nasabah Paket B',
        'alamat' => 'Jl. Paket No. 5',
        'jenis_kelamin' => 'perempuan',
        'didaftarkan_oleh' => $kolektorB->id,
        'status_pendaftaran' => 'aktif',
    ]);

    KolektorNasabah::create([
        'kolektor_id' => $kolektorB->id,
        'nasabah_id' => $nasabahB->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Sembako IDOR',
        'tipe' => 'paket',
        'harga_paket' => 1000000,
        'persen_komisi' => 0,
        'nominal_per_periode' => 10000,
        'status' => 'aktif',
    ]);

    $kepesertaanB = KepesertaanPaket::create([
        'nasabah_id' => $nasabahB->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'status_serah_terima' => 'belum',
        'metode_pengambilan' => 'diantar_kolektor',
    ]);

    // Kolektor A tidak boleh melihat item konfirmasi maupun detail bukti milik B
    $this->actingAs($kolektorA);

    Livewire::test(SerahTerimaPaket::class)
        ->set('konfirmasiId', $kepesertaanB->id)
        ->set('detailBuktiId', $kepesertaanB->id)
        ->assertViewHas('konfirmasiItem', null)
        ->assertViewHas('detailBuktiItem', null)
        ->assertViewHas('diserahkanOleh', null);

    // Kolektor B boleh melihat karena nasabah binaannya
    $this->actingAs($kolektorB);

    Livewire::test(SerahTerimaPaket::class)
        ->set('konfirmasiId', $kepesertaanB->id)
        ->set('detailBuktiId', $kepesertaanB->id)
        ->assertViewHas('konfirmasiItem', fn ($val) => $val !== null && $val->id === $kepesertaanB->id)
        ->assertViewHas('detailBuktiItem', fn ($val) => $val !== null && $val->id === $kepesertaanB->id);

    // Admin boleh melihat semua data
    $this->actingAs($admin);

    Livewire::test(SerahTerimaPaket::class)
        ->set('konfirmasiId', $kepesertaanB->id)
        ->set('detailBuktiId', $kepesertaanB->id)
        ->assertViewHas('konfirmasiItem', fn ($val) => $val !== null && $val->id === $kepesertaanB->id)
        ->assertViewHas('detailBuktiItem', fn ($val) => $val !== null && $val->id === $kepesertaanB->id);
});
