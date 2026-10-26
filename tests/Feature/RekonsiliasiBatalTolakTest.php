<?php

use App\Livewire\Admin\RekonsiliasiKas;
use App\Livewire\Kolektor\SetorKantor;
use App\Models\KolektorNasabah;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Livewire\Livewire;

function seedRekonRbt(): array
{
    $admin = User::factory()->admin()->create();
    $kolektor = User::factory()->kolektor()->create();
    $nasabah = User::factory()->nasabah()->create();

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Rekon Rbt',
        'alamat' => 'Jl. Test No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Produk Rekon Rbt',
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

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 0,
    ]);

    return compact('admin', 'kolektor', 'nasabah', 'produk');
}

function setorRbt(User $kolektor, User $nasabah, ProdukTabungan $produk, float $nominal): TransaksiSetoran
{
    SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->increment('saldo', $nominal);

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

it('membatalkan pengajuan pending kolektor melepas keterkaitan setoran', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);
    setorRbt($kolektor, $nasabah, $produk, 30000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');

    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();
    expect(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(2);

    Livewire::test(SetorKantor::class)->call('batalkan', $pengajuan->id);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('dibatalkan')
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(0)
        ->and(TransaksiSetoran::where('input_by', $kolektor->id)->whereNull('setoran_kolektor_id')->count())->toBe(2)
        ->and(LogAktivitas::where('aksi', 'batal_setoran_kantor')->count())->toBe(1);
});

it('tidak membatalkan pengajuan dua kali', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    Livewire::test(SetorKantor::class)->call('batalkan', $pengajuan->id);
    Livewire::test(SetorKantor::class)->call('batalkan', $pengajuan->id);

    expect($pengajuan->refresh()->status)->toBe('dibatalkan')
        ->and(LogAktivitas::where('aksi', 'batal_setoran_kantor')->count())->toBe(1);
});

it('tidak membolehkan kolektor lain membatalkan pengajuan yang bukan miliknya', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $kolektorLain = User::factory()->kolektor()->create();
    $this->actingAs($kolektorLain);
    Livewire::test(SetorKantor::class)->call('batalkan', $pengajuan->id);

    expect($pengajuan->refresh()->status)->toBe('pending')
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(1)
        ->and(LogAktivitas::where('aksi', 'batal_setoran_kantor')->count())->toBe(0);
});

it('menolak pengajuan pending oleh admin dengan alasan melepas keterkaitan dan memberi tahu kolektor', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->set('rejectAlasan', 'Kas fisik tidak lengkap')
        ->call('rejectSubmission');

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('dibatalkan')
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(0)
        ->and(LogAktivitas::where('aksi', 'tolak_setoran_kantor')->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $kolektor->id)
            ->where('pesan', 'like', '%Kas fisik tidak lengkap%')->count())->toBe(1);
});

it('mewajibkan alasan saat admin menolak pengajuan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->call('rejectSubmission')
        ->assertHasErrors(['rejectAlasan' => 'required']);

    expect($pengajuan->refresh()->status)->toBe('pending')
        ->and(LogAktivitas::where('aksi', 'tolak_setoran_kantor')->count())->toBe(0);
});

it('tidak menolak pengajuan dua kali', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->set('rejectAlasan', 'Tolak pertama')
        ->call('rejectSubmission');

    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->set('rejectAlasan', 'Tolak kedua')
        ->call('rejectSubmission');

    expect($pengajuan->refresh()->status)->toBe('dibatalkan')
        ->and(LogAktivitas::where('aksi', 'tolak_setoran_kantor')->count())->toBe(1);
});

it('tidak memproses rekonsiliasi untuk pengajuan yang sudah dibatalkan', function () {
    ['admin' => $admin, 'kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);
    Livewire::test(SetorKantor::class)->call('submit');
    $pengajuan = SetoranKolektorKantor::where('kolektor_id', $kolektor->id)->firstOrFail();

    $this->actingAs($admin);
    Livewire::test(RekonsiliasiKas::class)
        ->call('startReject', $pengajuan->id)
        ->set('rejectAlasan', 'Ditolak admin')
        ->call('rejectSubmission');

    Livewire::test(RekonsiliasiKas::class)
        ->set('processTotalDiterima', 50000)
        ->call('processSubmission', $pengajuan->id);

    $pengajuan->refresh();
    expect($pengajuan->status)->toBe('dibatalkan')
        ->and($pengajuan->total_diterima)->toBeNull()
        ->and(TransaksiSetoran::where('setoran_kolektor_id', $pengajuan->id)->count())->toBe(0);
});

it('memvalidasi panjang catatan penyerahan kas maksimal lima ratus karakter', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);

    $this->actingAs($kolektor);

    Livewire::test(SetorKantor::class)
        ->set('catatan', str_repeat('a', 501))
        ->call('submit')
        ->assertHasErrors(['catatan' => 'max']);

    expect(SetoranKolektorKantor::count())->toBe(0);
});

it('merangkum akumulasi selisih dan saldo berjalan per kolektor', function () {
    ['kolektor' => $kolektor, 'nasabah' => $nasabah, 'produk' => $produk] = seedRekonRbt();
    setorRbt($kolektor, $nasabah, $produk, 50000);
    setorRbt($kolektor, $nasabah, $produk, 30000);

    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->subDays(3)->toDateString(),
        'total_seharusnya' => 110000,
        'total_diterima' => 100000,
        'selisih' => -10000,
        'status' => 'kurang',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->subDays(2)->toDateString(),
        'total_seharusnya' => 50000,
        'total_diterima' => 55000,
        'selisih' => 5000,
        'status' => 'lebih',
    ]);
    SetoranKolektorKantor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal_setor' => now()->subDay()->toDateString(),
        'total_seharusnya' => 40000,
        'total_diterima' => 40000,
        'selisih' => 0,
        'status' => 'cocok',
    ]);

    $ringkas = SetoranKolektorKantor::ringkasKasPerKolektor();

    expect($ringkas[$kolektor->id]['selisih_kumulatif'])->toBe(-5000.0)
        ->and($ringkas[$kolektor->id]['saldo_berjalan'])->toBe(80000.0)
        ->and(array_key_exists($kolektor->id + 999, $ringkas))->toBeFalse();
});
