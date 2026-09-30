<?php

use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Models\KolektorNasabah;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

function createOfflineApprovedPenarikan(User $kolektor, User $nasabah): TransaksiPenarikan
{
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

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    return TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);
}

it('completes offline withdrawal with correct nasabah PIN', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $penarikan = createOfflineApprovedPenarikan($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;
    $result = $action->execute($penarikan, '123456', $kolektor);

    expect($result->status)->toBe('selesai');
    expect($result->diverifikasi_oleh)->toBe($kolektor->id);
    expect($result->metode_verifikasi)->toBe('pin_nasabah');
    expect($result->waktu_pencairan)->not->toBeNull();
    expect($result->percobaan_verifikasi_gagal)->toBe(0);
    expect($result->terkunci_hingga)->toBeNull();

    $this->assertDatabaseHas('log_notifikasi', [
        'nasabah_id' => $nasabah->id,
        'judul' => 'Penarikan Terverifikasi',
    ]);
});

it('rejects verification with wrong PIN and increments failed attempts', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $penarikan = createOfflineApprovedPenarikan($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $thrown = false;
    try {
        $action->execute($penarikan, '000000', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('PIN salah');
        expect($e->getMessage())->toContain('Sisa percobaan: 2');
    }

    expect($thrown)->toBeTrue();

    $penarikan->refresh();
    expect($penarikan->percobaan_verifikasi_gagal)->toBe(1);
    expect($penarikan->status)->toBe('approved');
});

it('locks verification after 3 failed PIN attempts', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $penarikan = createOfflineApprovedPenarikan($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    for ($i = 0; $i < 3; $i++) {
        try {
            $action->execute($penarikan, '000000', $kolektor);
        } catch (Exception $e) {
            // expected
        }
    }

    $penarikan->refresh();
    expect($penarikan->percobaan_verifikasi_gagal)->toBe(3);
    expect($penarikan->terkunci_hingga)->not->toBeNull();
    expect($penarikan->terkunci_hingga->isFuture())->toBeTrue();

    // Attempt 4 should be rejected even with correct PIN
    $thrown = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('dikunci');
    }

    expect($thrown)->toBeTrue();
});

it('allows verification after lockout expires', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    $penarikan = createOfflineApprovedPenarikan($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    // Trigger lockout
    for ($i = 0; $i < 3; $i++) {
        try {
            $action->execute($penarikan, '000000', $kolektor);
        } catch (Exception $e) {
            // expected
        }
    }

    // Advance time past lockout
    Carbon::setTestNow(now()->addMinutes(16));

    $result = $action->execute($penarikan, '123456', $kolektor);

    expect($result->status)->toBe('selesai');

    Carbon::setTestNow();
});

it('rejects verification from kolektor not assigned to nasabah', function () {
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

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $thrown = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('bukan tanggung jawab Anda');
    }

    expect($thrown)->toBeTrue();
    expect($penarikan->fresh()->status)->toBe('approved');
});

it('rejects verification for pending withdrawal', function () {
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

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'pending',
    ]);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $thrown = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('belum disetujui admin');
    }

    expect($thrown)->toBeTrue();
});

it('rejects verification for online withdrawal', function () {
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

    $produk = ProdukTabungan::create([
        'nama' => 'Tabungan Bebas',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
    ]);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $thrown = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('hanya berlaku untuk penarikan offline');
    }

    expect($thrown)->toBeTrue();
});

it('blocks admin from marking rumah_kolektor offline withdrawal as complete', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

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

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'rumah_kolektor',
        'status' => 'approved',
    ]);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('selesai', $penarikan->id);

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'approved',
    ]);
});

it('allows admin to mark kantor offline withdrawal as complete', function () {
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create();

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

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'offline',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'approved',
    ]);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    $this->assertDatabaseHas('transaksi_penarikan', [
        'id' => $penarikan->id,
        'status' => 'selesai',
        'metode_verifikasi' => 'manual_admin',
    ]);
});
