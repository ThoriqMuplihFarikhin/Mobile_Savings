<?php

use App\Livewire\Admin\AntrianKomplain;
use App\Livewire\Admin\ApprovalPenarikan;
use App\Livewire\Admin\MonitoringSetoran;
use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\KolektorNasabah;
use App\Models\Komplain;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedGuardTransaksi(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Guard',
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

    KolektorNasabah::create([
        'kolektor_id' => $kolektor->id,
        'nasabah_id' => $nasabah->id,
        'tanggal_mulai_ditangani' => now()->toDateString(),
        'status' => 'aktif',
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function setoranGuard(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal): TransaksiSetoran
{
    $saldo = SaldoProduk::firstOrCreate(
        ['nasabah_id' => $nasabah->id, 'produk_id' => $produk->id],
        ['saldo' => 0]
    );
    $saldo->increment('saldo', $nominal);

    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $kolektor->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
}

function komplainGuard(User $nasabah, string $status): Komplain
{
    return Komplain::create([
        'nasabah_id' => $nasabah->id,
        'kategori' => 'saldo',
        'deskripsi' => 'Saldo saya tidak sesuai dengan catatan setoran kemarin',
        'status' => $status,
        'tanggal_dibuat' => now(),
    ]);
}

function saldoGuard(User $nasabah, ProdukTabungan $produk): float
{
    return (float) SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->value('saldo');
}

it('menolak koreksi dengan nominal baru nol', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $setoran = setoranGuard($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 0)
        ->set('alasanKoreksi', 'Salah input')
        ->call('koreksi')
        ->assertHasErrors(['nominalBaru']);

    expect($setoran->refresh()->status)->toBe('tercatat')
        ->and(saldoGuard($nasabah, $produk))->toBe(50000.00);
});

it('menolak koreksi yang membuat saldo nasabah negatif', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $setoran = setoranGuard($kolektor, $nasabah, $produk, 50000);

    SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->update(['saldo' => 10000]);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 1000)
        ->set('alasanKoreksi', 'Koreksi penyesuaian')
        ->call('koreksi')
        ->assertHasNoErrors();

    expect($setoran->refresh()->status)->toBe('tercatat')
        ->and(saldoGuard($nasabah, $produk))->toBe(10000.00);
});

it('menolak pembatalan yang membuat saldo nasabah negatif', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $setoran = setoranGuard($kolektor, $nasabah, $produk, 50000);

    SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->update(['saldo' => 10000]);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal')
        ->assertHasNoErrors();

    expect($setoran->refresh()->status)->toBe('tercatat')
        ->and(saldoGuard($nasabah, $produk))->toBe(10000.00);
});

it('koreksi dua kali hanya mengubah uang sekali', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $setoran = setoranGuard($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 60000)
        ->set('alasanKoreksi', 'Salah ketik')
        ->call('koreksi')
        ->assertHasNoErrors();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleKoreksi', $setoran->id)
        ->set('nominalBaru', 70000)
        ->set('alasanKoreksi', 'Koreksi ulang')
        ->call('koreksi')
        ->assertHasNoErrors();

    $setoran->refresh();
    expect($setoran->status)->toBe('dikoreksi')
        ->and((float) $setoran->nominal)->toBe(60000.00)
        ->and(saldoGuard($nasabah, $produk))->toBe(60000.00);
});

it('pembatalan dua kali hanya mengurangi saldo sekali', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $setoran = setoranGuard($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($admin);
    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Duplikat input')
        ->call('batal')
        ->assertHasNoErrors();

    Livewire::test(MonitoringSetoran::class)
        ->call('toggleBatal', $setoran->id)
        ->set('alasanBatal', 'Batal lagi')
        ->call('batal')
        ->assertHasNoErrors();

    expect($setoran->refresh()->status)->toBe('dibatalkan')
        ->and(saldoGuard($nasabah, $produk))->toBe(0.00);
});

it('approve penarikan dua kali hanya mendebet saldo sekali', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    setoranGuard($kolektor, $nasabah, $produk, 100000);

    $penarikan = TransaksiPenarikan::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'nominal_diminta' => 50000,
        'persen_komisi_terpakai' => 5.00,
        'nominal_komisi' => 2500,
        'nominal_diterima' => 47500,
        'jalur_pengajuan' => 'online',
        'lokasi_pengambilan' => 'kantor',
        'status' => 'pending',
    ]);

    $this->actingAs($admin);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);
    Livewire::test(ApprovalPenarikan::class)->call('approve', $penarikan->id);

    expect($penarikan->refresh()->status)->toBe('approved')
        ->and(saldoGuard($nasabah, $produk))->toBe(50000.00);
});

it('penyelesaian manual penarikan dua kali hanya mencatat satu log', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();

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

    $this->actingAs($admin);
    Livewire::test(ApprovalPenarikan::class)->set('alasan', 'Pencairan dikonfirmasi di kantor')->call('selesai', $penarikan->id);
    Livewire::test(ApprovalPenarikan::class)->set('alasan', 'Pencairan dikonfirmasi di kantor')->call('selesai', $penarikan->id);

    expect($penarikan->refresh()->status)->toBe('selesai')
        ->and(LogAktivitas::where('aksi', 'selesai_penarikan')
            ->where('entitas_id', $penarikan->id)->count())->toBe(1);
});

it('memproses komplain dua kali hanya mengirim satu notifikasi', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $komplain = komplainGuard($nasabah, 'baru');

    $this->actingAs($admin);
    Livewire::test(AntrianKomplain::class)->call('proses', $komplain->id);
    Livewire::test(AntrianKomplain::class)->call('proses', $komplain->id);

    expect($komplain->refresh()->status)->toBe('diproses')
        ->and(LogNotifikasi::where('nasabah_id', $nasabah->id)
            ->where('judul', 'Komplain Diproses')->count())->toBe(1);
});

it('komplain yang sudah selesai tidak bisa diproses ulang', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $komplain = komplainGuard($nasabah, 'selesai');

    $this->actingAs($admin);
    Livewire::test(AntrianKomplain::class)->call('proses', $komplain->id);

    expect($komplain->refresh()->status)->toBe('selesai');
});

it('komplain berstatus baru tidak bisa langsung diselesaikan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    $komplain = komplainGuard($nasabah, 'baru');

    $this->actingAs($admin);
    Livewire::test(AntrianKomplain::class)
        ->set('catatan', 'Sudah ditangani')
        ->call('selesai', $komplain->id)
        ->assertHasNoErrors();

    expect($komplain->refresh()->status)->toBe('baru');
});

it('proses pengajuan rekon dua kali ditolak dan nilai tidak berubah', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedGuardTransaksi();
    setoranGuard($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->set('processTotalDiterima', 50000)
        ->call('processSubmission', $pengajuan->id)
        ->assertHasNoErrors();

    Livewire::test(RekonsiliasiKas::class)
        ->set('processTotalDiterima', 60000)
        ->set('processKeterangan', 'Input ulang')
        ->call('processSubmission', $pengajuan->id)
        ->assertHasNoErrors();

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('cocok')
        ->and((float) $pengajuan->total_diterima)->toBe(50000.00);
});
