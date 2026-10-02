<?php

use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\Laporan;
use App\Livewire\Kolektor\DaftarNasabah;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

function buatPenarikanRumahD3(User $kolektor, User $nasabah): TransaksiPenarikan
{
    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah D3',
        'alamat' => 'Jl. D3 No. 1',
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
        'nama' => 'Tabungan Bebas D3',
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

it('menolak verifikasi pin offline untuk nasabah yang belum ganti pin', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);

    $penarikan = buatPenarikanRumahD3($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $thrown = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $thrown = true;
        expect($e->getMessage())->toContain('belum mengganti PIN awal');
    }

    $penarikan->refresh();
    expect($thrown)->toBeTrue()
        ->and($penarikan->status)->toBe('approved')
        ->and($penarikan->percobaan_verifikasi_gagal)->toBe(0);
});

it('menerima verifikasi setelah nasabah mengganti pin', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);

    $penarikan = buatPenarikanRumahD3($kolektor, $nasabah);

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;

    $diblokir = false;
    try {
        $action->execute($penarikan, '123456', $kolektor);
    } catch (Exception $e) {
        $diblokir = true;
    }

    expect($diblokir)->toBeTrue()
        ->and($penarikan->fresh()->status)->toBe('approved');

    $nasabah->update(['harus_ganti_pin' => false]);

    $result = $action->execute($penarikan, '123456', $kolektor);

    expect($result->status)->toBe('selesai')
        ->and($penarikan->fresh()->metode_verifikasi)->toBe('pin_nasabah');
});

it('mencatat override admin pencairan rumah kolektor dengan penanda risiko tinggi', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);

    $penarikan = buatPenarikanRumahD3($kolektor, $nasabah);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Nasabah terbaring sakit, pencairan diverifikasi manual di rumah')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    $penarikan->refresh();
    expect($penarikan->status)->toBe('selesai')
        ->and($penarikan->metode_verifikasi)->toBe('manual_admin');

    $log = LogAktivitas::where('aksi', 'selesai_penarikan_override')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->detail['risiko_tinggi'] ?? null)->toBeTrue()
        ->and($log->detail['alasan'] ?? null)->toBe('Nasabah terbaring sakit, pencairan diverifikasi manual di rumah');

    Livewire::test(Laporan::class)
        ->assertSee('override berisiko');
});

it('menolak override admin bila nasabah sudah mengganti pin', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => false]);

    $penarikan = buatPenarikanRumahD3($kolektor, $nasabah);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Alasan apapun')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    expect($penarikan->fresh()->status)->toBe('approved')
        ->and(LogAktivitas::where('aksi', 'selesai_penarikan_override')->exists())->toBeFalse();
});

it('memberi peringatan ganti pin pada layar kolektor setelah pendaftaran nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();

    $this->actingAs($kolektor);

    Livewire::test(DaftarNasabah::class)
        ->set('nama', 'Nasabah Peringatan')
        ->set('noHp', '081234567895')
        ->set('alamat', 'Jl. Peringatan No. 5')
        ->set('tanggalLahir', '1990-01-01')
        ->set('jenisKelamin', 'laki-laki')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSee('mengganti PIN');
});
