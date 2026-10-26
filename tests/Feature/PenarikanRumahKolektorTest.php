<?php

use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Kolektor\PenarikanOffline;
use App\Livewire\Kolektor\VerifikasiPenarikan;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;

function produkRumahP31(): ProdukTabungan
{
    return ProdukTabungan::create([
        'nama' => 'Tabungan Rumah P31',
        'tipe' => 'bebas',
        'persen_komisi' => 5.00,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);
}

function binaanRumahP31(User $kolektor, User $nasabah): void
{
    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);
}

function penarikanRumahP31(User $nasabah, ProdukTabungan $produk, string $jalur, string $lokasi, string $status = 'approved'): TransaksiPenarikan
{
    return TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => $jalur,
        'lokasi_pengambilan' => $lokasi,
        'status' => $status,
    ]);
}

/**
 * D13: kas fisik kolektor Rp 100.000 agar guard kas di tangan cukup
 * membayar penarikan tunai (47.500) pada tes yang menyelesaikan penarikan.
 */
function kasRumahP31(User $kolektor, User $nasabah, ProdukTabungan $produk): void
{
    TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => 100000,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
}

it('menampilkan penarikan online rumah kolektor hanya di daftar kolektor yang bertanggung jawab', function () {
    $kolektorA = User::factory()->kolektor()->create();
    $kolektorB = User::factory()->kolektor()->create();
    $nasabahX = User::factory()->nasabah()->create();
    $nasabahY = User::factory()->nasabah()->create();
    $produk = produkRumahP31();

    binaanRumahP31($kolektorA, $nasabahX);
    binaanRumahP31($kolektorB, $nasabahY);

    $penarikan = penarikanRumahP31($nasabahX, $produk, 'online', 'rumah_kolektor');

    Livewire::actingAs($kolektorA)
        ->test(VerifikasiPenarikan::class)
        ->assertViewHas('penarikan', fn ($daftar) => $daftar->getCollection()->pluck('id')->contains($penarikan->id));

    Livewire::actingAs($kolektorB)
        ->test(VerifikasiPenarikan::class)
        ->assertViewHas('penarikan', fn ($daftar) => ! $daftar->getCollection()->pluck('id')->contains($penarikan->id));
});

it('memverifikasi penarikan online rumah kolektor dengan pin nasabah', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);
    kasRumahP31($kolektor, $nasabah, $produk);

    $penarikan = penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');

    Auth::login($kolektor);
    $action = new VerifikasiPenarikanOfflineAction;
    $hasil = $action->execute($penarikan, '123456', $kolektor);

    expect($hasil->status)->toBe('selesai')
        ->and($hasil->metode_verifikasi)->toBe('pin_nasabah')
        ->and($hasil->diverifikasi_oleh)->toBe($kolektor->id)
        ->and(LogAktivitas::where('aksi', 'verifikasi_penarikan_offline')
            ->where('entitas_id', $penarikan->id)->count())->toBe(1);
});

it('mencatat override admin penarikan online rumah kolektor dengan penanda override_rumah', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);
    kasRumahP31($kolektor, $nasabah, $produk);

    $penarikan = penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Nasabah terbaring sakit, pencairan diverifikasi manual di rumah')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    expect($penarikan->refresh()->status)->toBe('selesai');

    $log = LogAktivitas::where('aksi', 'selesai_penarikan_override')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->detail['override_rumah'] ?? null)->toBeTrue()
        ->and($log->detail['risiko_tinggi'] ?? null)->toBeTrue();
});

it('menolak override admin penarikan online rumah kolektor bila nasabah sudah ganti pin', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => false]);
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);

    $penarikan = penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'Alasan apapun')
        ->call('selesai', $penarikan->id)
        ->assertHasNoErrors();

    expect($penarikan->fresh()->status)->toBe('approved')
        ->and(LogAktivitas::where('aksi', 'selesai_penarikan_override')->exists())->toBeFalse();
});

it('mewajibkan alasan minimal 10 karakter untuk override rumah kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create(['harus_ganti_pin' => true]);
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);

    $penarikan = penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->set('alasan', 'alasan')
        ->call('selesai', $penarikan->id);

    expect($penarikan->refresh()->status)->toBe('approved')
        ->and(LogAktivitas::where('aksi', 'selesai_penarikan_override')->exists())->toBeFalse();
});

it('mencatat kolektor_id penanggung jawab pada log approve penarikan rumah kolektor', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);

    $penarikan = penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor', 'pending');

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 100000,
    ]);

    $this->actingAs($admin);

    Livewire::test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id);

    $log = LogAktivitas::where('aksi', 'approve_penarikan')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($log)->not->toBeNull()
        ->and($log->detail['kolektor_id'] ?? null)->toBe($kolektor->id);
});

it('menghitung penarikan menunggu diantar di nav card penarikan offline termasuk jalur online', function () {
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);

    penarikanRumahP31($nasabah, $produk, 'offline', 'rumah_kolektor');
    penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');

    $this->actingAs($kolektor);

    Livewire::test(PenarikanOffline::class)
        ->assertSet('jumlahMenungguVerifikasi', 2);
});

it('menampilkan jumlah penarikan menunggu diantar di dashboard kolektor', function () {
    $kolektor = User::factory()->kolektor()->create();
    $kolektorLain = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();
    $nasabahLain = User::factory()->nasabah()->create();
    $produk = produkRumahP31();

    binaanRumahP31($kolektor, $nasabah);
    binaanRumahP31($kolektorLain, $nasabahLain);

    penarikanRumahP31($nasabah, $produk, 'online', 'rumah_kolektor');
    penarikanRumahP31($nasabah, $produk, 'offline', 'rumah_kolektor');
    penarikanRumahP31($nasabahLain, $produk, 'online', 'rumah_kolektor');

    $this->actingAs($kolektor)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewHas('penarikanMenungguDiantar', 2);
});
