<?php

use App\Livewire\Admin\NasabahBermasalah;
use App\Models\KepesertaanPaket;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use Livewire\Livewire;

function seedKepesertaanReview(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['notifikasi_wa_aktif' => false]);

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Review',
        'alamat' => 'Jl. Review No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Review',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 50000,
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->subDays(10)->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 7,
        'status_alert' => 'perlu_review',
    ]);

    return compact('admin', 'nasabah', 'produk', 'kepesertaan');
}

function saldoReview(User $nasabah, ProdukTabungan $produk): float
{
    return (float) (SaldoProduk::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->value('saldo') ?? 0);
}

it('lanjut menunda perhitungan tanpa mengisi keputusan_akhir dan menurunkan alert', function () {
    ['admin' => $admin, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'lanjut')
        ->set('ditunda_hingga', now()->addDays(30)->toDateString())
        ->set('catatan_admin', 'Nasabah berjanji setor minggu depan')
        ->call('updateKeputusan')
        ->assertHasNoErrors();

    $kepesertaan->refresh();
    expect($kepesertaan->keputusan_akhir)->toBeNull()
        ->and($kepesertaan->ditunda_hingga->toDateString())->toBe(now()->addDays(30)->toDateString())
        ->and($kepesertaan->status_alert)->not->toBe('perlu_review');

    expect(LogAktivitas::where('aksi', 'keputusan_paket')
        ->where('entitas_id', $kepesertaan->id)->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $kepesertaan->nasabah_id)->count())->toBe(1);
});

it('lanjut menolak ditunda lebih dari 90 hari', function () {
    ['admin' => $admin, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'lanjut')
        ->set('ditunda_hingga', now()->addDays(100)->toDateString())
        ->call('updateKeputusan')
        ->assertHasErrors(['ditunda_hingga']);
});

it('gagal_dikembalikan membuat penarikan offline pending senilai seluruh saldo', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dikembalikan')
        ->set('metode_pengambilan', 'diantar_kolektor')
        ->set('catatan_admin', 'Nasabah pindah domisili')
        ->call('updateKeputusan')
        ->assertHasNoErrors();

    $penarikan = TransaksiPenarikan::where('nasabah_id', $nasabah->id)
        ->where('produk_id', $produk->id)
        ->sole();

    expect((float) $penarikan->nominal_diminta)->toBe(50000.0)
        ->and((float) $penarikan->nominal_diterima)->toBe(50000.0)
        ->and((float) $penarikan->persen_komisi_terpakai)->toBe(0.0)
        ->and($penarikan->jalur_pengajuan)->toBe('offline')
        ->and($penarikan->lokasi_pengambilan)->toBe('rumah_kolektor')
        ->and($penarikan->status)->toBe('pending');

    $kepesertaan->refresh();
    expect($kepesertaan->keputusan_akhir)->toBe('gagal_dikembalikan')
        ->and($kepesertaan->metode_pengambilan)->toBe('diantar_kolektor')
        ->and($kepesertaan->catatan_admin)->toBe('Nasabah pindah domisili');

    expect(LogAktivitas::where('aksi', 'keputusan_paket')
        ->where('entitas_id', $kepesertaan->id)->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $nasabah->id)->count())->toBe(1);
});

it('keputusan ganda pada kepesertaan yang sudah diputuskan ditolak', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dikembalikan')
        ->set('metode_pengambilan', 'ambil_sendiri')
        ->call('updateKeputusan')
        ->assertHasNoErrors();

    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dikembalikan')
        ->set('metode_pengambilan', 'ambil_sendiri')
        ->call('updateKeputusan');

    expect(TransaksiPenarikan::where('nasabah_id', $nasabah->id)->count())->toBe(1)
        ->and(LogAktivitas::where('aksi', 'keputusan_paket')
            ->where('entitas_id', $kepesertaan->id)->count())->toBe(1);
});

it('updateKeputusan ganda tanpa memilih ulang tidak membuat penarikan kedua', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    $halaman = Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dikembalikan')
        ->set('metode_pengambilan', 'ambil_sendiri');

    $halaman->call('updateKeputusan')->assertHasNoErrors();
    $halaman->call('updateKeputusan');

    expect(TransaksiPenarikan::where('nasabah_id', $nasabah->id)->count())->toBe(1);
});

it('gagal_dialihkan memindahkan seluruh saldo ke produk tujuan dan membuat dua log', function () {
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $produkTujuan = ProdukTabungan::create([
        'nama' => 'Bebas Tujuan',
        'tipe' => 'bebas',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'status' => 'aktif',
    ]);

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dialihkan')
        ->set('produkTujuanId', $produkTujuan->id)
        ->set('catatan_admin', 'Alih ke tabungan bebas')
        ->call('updateKeputusan')
        ->assertHasNoErrors();

    expect(saldoReview($nasabah, $produk))->toBe(0.0)
        ->and(saldoReview($nasabah, $produkTujuan))->toBe(50000.0);

    $kepesertaan->refresh();
    expect($kepesertaan->keputusan_akhir)->toBe('gagal_dialihkan')
        ->and(TransaksiPenarikan::where('nasabah_id', $nasabah->id)->count())->toBe(0);

    expect(LogAktivitas::where('aksi', 'keputusan_paket')
        ->where('entitas_id', $kepesertaan->id)->count())->toBe(1)
        ->and(LogAktivitas::where('aksi', 'alih_saldo_paket')
            ->where('entitas_id', $kepesertaan->id)->count())->toBe(1)
        ->and(LogNotifikasi::where('nasabah_id', $nasabah->id)->count())->toBe(1);
});

it('gagal_dialihkan menolak produk tujuan bukan aktif atau produk yang sama', function () {
    ['admin' => $admin, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', $kepesertaan->id)
        ->set('keputusan_akhir', 'gagal_dialihkan')
        ->set('produkTujuanId', $produk->id)
        ->call('updateKeputusan');

    expect($kepesertaan->refresh()->keputusan_akhir)->toBeNull();
});

it('selectKepesertaan id yang tidak ada memberi flash error tanpa 500', function () {
    ['admin' => $admin] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->call('selectKepesertaan', 999999)
        ->assertSet('showDetail', false);
});

it('updateKeputusan tanpa memilih kepesertaan memberi flash error tanpa error', function () {
    ['admin' => $admin] = seedKepesertaanReview();

    $this->actingAs($admin);
    Livewire::test(NasabahBermasalah::class)
        ->set('keputusan_akhir', 'lanjut')
        ->set('ditunda_hingga', now()->addDays(10)->toDateString())
        ->call('updateKeputusan')
        ->assertSet('showDetail', false);
});
