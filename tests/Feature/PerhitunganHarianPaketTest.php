<?php

use App\Models\KepesertaanPaket;
use App\Models\LogNotifikasi;
use App\Models\NasabahProfil;
use App\Models\ProdukTabungan;
use App\Models\SaldoProduk;
use App\Models\TransaksiSetoran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

afterEach(fn () => Carbon::setTestNow());

function seedHarianPaket(): array
{
    $admin = User::factory()->admin()->create();
    $nasabah = User::factory()->nasabah()->create(['notifikasi_wa_aktif' => false]);

    NasabahProfil::create([
        'user_id' => $nasabah->id,
        'nama' => 'Nasabah Harian',
        'alamat' => 'Jl. Harian No. 1',
        'jenis_kelamin' => 'laki-laki',
        'didaftarkan_oleh' => $admin->id,
        'status_pendaftaran' => 'aktif',
    ]);

    $produk = ProdukTabungan::create([
        'nama' => 'Paket Harian',
        'tipe' => 'paket',
        'persen_komisi' => 0,
        'minimal_setor' => 10000,
        'harga_per_hari' => 10000,
        'status' => 'aktif',
    ]);

    SaldoProduk::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'saldo' => 0,
    ]);

    $kepesertaan = KepesertaanPaket::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'tanggal_mulai_ikut' => now()->toDateString(),
        'total_seharusnya_terkumpul' => 0,
        'total_aktual_terkumpul' => 0,
        'tunggakan' => 0,
        'status_alert' => 'normal',
    ]);

    return compact('admin', 'nasabah', 'produk', 'kepesertaan');
}

function setoranHarian(User $admin, User $nasabah, ProdukTabungan $produk, KepesertaanPaket $kepesertaan, float $nominal): TransaksiSetoran
{
    return TransaksiSetoran::create([
        'nasabah_id' => $nasabah->id,
        'produk_id' => $produk->id,
        'kepesertaan_id' => $kepesertaan->id,
        'nominal' => $nominal,
        'tanggal_transaksi' => now()->toDateString(),
        'tanggal_input_sistem' => now(),
        'input_by' => $admin->id,
        'sumber_input' => 'real_time',
        'status' => 'tercatat',
    ]);
}

it('paket:hitung-ulang menaikkan status alert setelah nasabah berhenti menabung 10 hari', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-03 07:00:00'));
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedHarianPaket();

    setoranHarian($admin, $nasabah, $produk, $kepesertaan, 10000);
    $kepesertaan->hitungUlangKepesertaan();

    expect((int) $kepesertaan->refresh()->tunggakan)->toBe(0)
        ->and($kepesertaan->status_alert)->toBe('normal');

    Carbon::setTestNow(Carbon::parse('2026-10-13 00:10:00'));

    Artisan::call('paket:hitung-ulang');

    $kepesertaan->refresh();
    expect((int) $kepesertaan->tunggakan)->toBe(10)
        ->and($kepesertaan->status_alert)->toBe('peringatan');
});

it('paket:hitung-ulang idempoten bila dijalankan dua kali', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-03 07:00:00'));
    ['admin' => $admin, 'nasabah' => $nasabah, 'produk' => $produk, 'kepesertaan' => $kepesertaan] = seedHarianPaket();

    setoranHarian($admin, $nasabah, $produk, $kepesertaan, 30000);

    Carbon::setTestNow(Carbon::parse('2026-10-13 00:10:00'));

    Artisan::call('paket:hitung-ulang');
    $pertama = $kepesertaan->refresh()
        ->only(['tunggakan', 'status_alert', 'total_aktual_terkumpul', 'total_seharusnya_terkumpul']);

    Artisan::call('paket:hitung-ulang');
    $kedua = $kepesertaan->refresh()
        ->only(['tunggakan', 'status_alert', 'total_aktual_terkumpul', 'total_seharusnya_terkumpul']);

    expect($kedua)->toBe($pertama);
});

it('paket:kirim-pengingat tidak mengirim notifikasi dua kali dalam sehari', function () {
    Carbon::setTestNow(Carbon::parse('2026-10-13 08:00:00'));
    ['nasabah' => $nasabah, 'kepesertaan' => $kepesertaan] = seedHarianPaket();
    $kepesertaan->update(['tunggakan' => 5, 'status_alert' => 'peringatan']);

    Artisan::call('paket:kirim-pengingat');
    Artisan::call('paket:kirim-pengingat');

    $notifikasi = LogNotifikasi::where('nasabah_id', $nasabah->id)->get();

    expect($notifikasi)->toHaveCount(1)
        ->and($notifikasi->first()->judul)->toBe('Pengingat Tunggakan')
        ->and($notifikasi->first()->pesan)->toContain('Paket Harian');
});

it('kartu perlu review di dashboard admin tertaut ke daftar nasabah bermasalah', function () {
    ['admin' => $admin, 'kepesertaan' => $kepesertaan] = seedHarianPaket();
    $kepesertaan->update(['tunggakan' => 7, 'status_alert' => 'perlu_review']);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Perlu review')
        ->assertSee('/admin/bermasalah', false);
});
