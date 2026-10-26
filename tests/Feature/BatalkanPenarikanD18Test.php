<?php

use App\Actions\Penarikan\BatalkanPenarikanAction;
use App\Actions\Penarikan\VerifikasiPenarikanOfflineAction;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\Komisi;
use App\Livewire\Nasabah\AjukanPenarikan;
use App\Livewire\Nasabah\PengajuanAktif;
use App\Livewire\Nasabah\RiwayatPenarikan;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Fixture D18: penarikan + saldo sesuai status (approved = saldo sudah dipotong
 * approve sebesar nominal_diminta, sesuai ApprovalPenarikan::approve).
 *
 * @return array{nasabah: User, produk: ProdukTabungan, penarikan: TransaksiPenarikan, kolektor: User|null}
 */
function d18Fixture(array $opsi = []): array
{
    $status = $opsi['status'] ?? 'pending';
    $nasabah = User::factory()->nasabah()->create();

    $produk = ProdukTabungan::create([
        'nama' => 'Bebas D18',
        'tipe' => 'bebas',
        'persen_komisi' => $opsi['persen_komisi'] ?? 0,
        'status' => 'aktif',
    ]);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 100000,
        'persen_komisi_terpakai' => $opsi['persen_komisi'] ?? 0,
        'nominal_komisi' => $opsi['nominal_komisi'] ?? 0,
        'nominal_diterima' => 100000 - ($opsi['nominal_komisi'] ?? 0),
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => $opsi['lokasi'] ?? 'kantor',
        'status' => $status,
        'disetujui_oleh' => $opsi['disetujui_oleh'] ?? null,
        'waktu_approval' => $status === 'approved' || $status === 'selesai' ? now() : null,
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => $opsi['saldo'] ?? ($status === 'approved' ? 400000 : 500000),
    ]);

    $kolektor = $opsi['kolektor_pj'] ?? null;

    if ($kolektor instanceof User) {
        KolektorNasabah::create([
            'kolektor_id' => $kolektor->id,
            'nasabah_id' => $nasabah->id,
            'tanggal_mulai_ditangani' => now()->toDateString(),
            'status' => 'aktif',
        ]);
    }

    if (isset($opsi['umur_hari'])) {
        DB::table('transaksi_penarikan')
            ->where('id', $penarikan->id)
            ->update(['created_at' => now()->subDays($opsi['umur_hari'])]);
    }

    return compact('nasabah', 'produk', 'penarikan', 'kolektor');
}

it('membatalkan penarikan pending mengisi kolom pembatalan alasan dan log detail', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture();

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->set('alasanBatal', 'Butuh dana darurat')
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    $segars = $penarikan->fresh();
    $log = LogAktivitas::where('aksi', 'batalkan_penarikan')
        ->where('entitas_terkait', 'transaksi_penarikan')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($segars->status)->toBe('dibatalkan')
        ->and((int) $segars->dibatalkan_oleh)->toBe((int) $nasabah->id)
        ->and($segars->alasan_batal)->toBe('Butuh dana darurat')
        ->and($segars->waktu_dibatalkan)->not->toBeNull()
        ->and($log)->not->toBeNull()
        ->and($log->detail['status_sebelumnya'] ?? null)->toBe('pending')
        ->and($log->detail['saldo_dikembalikan'] ?? null)->toBeFalse();
});

it('pembatalan penarikan fase-1 approval ganda tidak mengembalikan saldo', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture(['disetujui_oleh' => $admin->id]);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    $log = LogAktivitas::where('aksi', 'batalkan_penarikan')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0)
        ->and($log->detail['saldo_dikembalikan'] ?? null)->toBeFalse();
});

it('pembatalan penarikan approved mengembalikan saldo tepat nominal_diminta', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture(['status' => 'approved']);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    $log = LogAktivitas::where('aksi', 'batalkan_penarikan')
        ->where('entitas_id', $penarikan->id)
        ->first();

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0)
        ->and($log->detail['status_sebelumnya'] ?? null)->toBe('approved')
        ->and($log->detail['saldo_dikembalikan'] ?? null)->toBeTrue();
});

it('menolak pembatalan penarikan berstatus selesai ditolak dibatalkan dan kedaluwarsa', function () {
    foreach (['selesai', 'ditolak', 'dibatalkan', 'kedaluwarsa'] as $status) {
        ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture(['status' => $status]);

        Livewire::actingAs($nasabah)
            ->test(RiwayatPenarikan::class)
            ->call('batalkan', $penarikan->id)
            ->assertSee('tidak dapat dibatalkan');

        expect($penarikan->fresh()->status)->toBe($status);
    }
});

it('aksi pembatalan langsung menolak pihak yang bukan pemilik penarikan', function () {
    ['penarikan' => $penarikan] = d18Fixture();
    $lain = User::factory()->nasabah()->create();

    expect(fn () => app(BatalkanPenarikanAction::class)->execute($penarikan, $lain))
        ->toThrow(DomainException::class, 'Pengajuan penarikan tidak dapat dibatalkan.');

    expect($penarikan->fresh()->status)->toBe('pending');
});

it('pembatalan sebelum verifikasi PIN membuat verifikasi gagal dengan aman', function () {
    $kolektor = User::factory()->kolektor()->create();
    ['penarikan' => $penarikan] = d18Fixture([
        'status' => 'approved',
        'lokasi' => 'rumah_kolektor',
        'kolektor_pj' => $kolektor,
    ]);
    $nasabah = $penarikan->nasabah;

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    expect(fn () => app(VerifikasiPenarikanOfflineAction::class)
        ->execute($penarikan->fresh(), '123456', $kolektor))
        ->toThrow(Exception::class, 'Penarikan ini belum disetujui admin atau sudah diproses.');

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and(LogAktivitas::where('aksi', 'verifikasi_penarikan_offline')
            ->where('entitas_id', $penarikan->id)->exists())->toBeFalse();
});

it('pembatalan sebelum selesai admin membuat selesai gagal dengan aman', function () {
    $admin = User::factory()->admin()->create();
    ['penarikan' => $penarikan] = d18Fixture(['status' => 'approved']);

    Livewire::actingAs($penarikan->nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    Livewire::actingAs($admin)
        ->test(ApprovalPenarikan::class)
        ->set('alasan', 'Alasan override minimal sepuluh karakter')
        ->call('selesai', $penarikan->id);

    expect($penarikan->fresh()->status)->toBe('dibatalkan');
});

it('pembatalan sebelum approve admin membuat approve gagal dengan aman', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture();

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    Livewire::actingAs($admin)
        ->test(ApprovalPenarikan::class)
        ->call('approve', $penarikan->id);

    expect($penarikan->fresh()->status)->toBe('dibatalkan')
        ->and((float) SaldoProduk::where('nasabah_id', $nasabah->id)->value('saldo'))->toBe(500000.0)
        ->and(LogAktivitas::where('aksi', 'approve_penarikan')
            ->where('entitas_id', $penarikan->id)->exists())->toBeFalse();
});

it('penarikan approved yang dibatalkan keluar dari perhitungan komisi admin', function () {
    $admin = User::factory()->admin()->create();
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture([
        'status' => 'approved',
        'persen_komisi' => 5,
        'nominal_komisi' => 5000,
    ]);

    Livewire::actingAs($admin)
        ->test(Komisi::class)
        ->assertSet('totalKomisi', 5000.0);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    Livewire::actingAs($admin)
        ->test(Komisi::class)
        ->assertSet('totalKomisi', 0.0);
});

it('command kedaluwarsakan mengabaikan penarikan yang sudah dibatalkan', function () {
    ['penarikan' => $dibatalkan] = d18Fixture(['umur_hari' => 9]);
    $pendingTua = d18Fixture(['umur_hari' => 8]);

    Livewire::actingAs($dibatalkan->nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $dibatalkan->id)
        ->assertSee('berhasil dibatalkan');

    $this->artisan('penarikan:kedaluwarsakan')->assertSuccessful();

    expect($dibatalkan->fresh()->status)->toBe('dibatalkan')
        ->and($pendingTua['penarikan']->fresh()->status)->toBe('kedaluwarsa');
});

it('pembatalan mengirim notifikasi in-app ke admin dan kolektor penanggung jawab', function () {
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture([
        'status' => 'approved',
        'lokasi' => 'rumah_kolektor',
        'kolektor_pj' => $kolektor,
    ]);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id)
        ->assertSee('berhasil dibatalkan');

    expect(LogNotifikasi::where('nasabah_id', $admin->id)
        ->where('judul', 'Pengajuan Penarikan Dibatalkan')
        ->where('channel', 'in_app')->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $kolektor->id)
            ->where('judul', 'Pengajuan Penarikan Dibatalkan')
            ->where('channel', 'in_app')->count())->toBe(1);
});

it('tombol riwayat penarikan juga tampil untuk status approved', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture(['status' => 'approved']);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->assertSee('Batalkan');

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->call('batalkan', $penarikan->id);

    Livewire::actingAs($nasabah)
        ->test(RiwayatPenarikan::class)
        ->assertDontSee('Batalkan');
});

it('kartu pengajuan aktif di halaman ajukan penarikan menawarkan pembatalan', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture();

    Livewire::actingAs($nasabah)
        ->test(AjukanPenarikan::class)
        ->assertSee('Pengajuan Aktif');

    Livewire::actingAs($nasabah)
        ->test(PengajuanAktif::class)
        ->call('batalkan', $penarikan->id);

    expect($penarikan->fresh()->status)->toBe('dibatalkan');
});

it('kartu pengajuan aktif tampil di dashboard nasabah dan hilang setelah dibatalkan', function () {
    ['nasabah' => $nasabah, 'penarikan' => $penarikan] = d18Fixture();

    $this->actingAs($nasabah)
        ->get('/dashboard')
        ->assertOk()
        ->assertSee('Pengajuan Aktif');

    Livewire::actingAs($nasabah)
        ->test(PengajuanAktif::class)
        ->call('batalkan', $penarikan->id);

    $this->actingAs($nasabah)
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('Pengajuan Aktif');
});

it('kartu pengajuan aktif tidak tampil bila nasabah tidak memiliki pengajuan berjalan', function () {
    ['nasabah' => $nasabah] = d18Fixture(['status' => 'selesai']);

    Livewire::actingAs($nasabah)
        ->test(PengajuanAktif::class)
        ->assertDontSee('Pengajuan Aktif');
});
