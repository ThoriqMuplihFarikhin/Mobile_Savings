<?php

use App\Actions\Penarikan\BatalkanPenarikanAction;
use App\Models\KepesertaanPaket;
use App\Models\ProdukTabungan;
use App\Models\TransaksiPenarikan;
use App\Models\User;
use App\Support\KasKolektorHitung;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Artisan;

it('membuat data demo lengkap: kas kolektor, nasabah offline, paket berharga, komitmen, penarikan approved', function () {
    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    $kolektor = User::where('name', 'Kolektor Demo')->first();
    expect($kolektor)->not->toBeNull()
        ->and(KasKolektorHitung::kasDiTangan($kolektor->id))->toBe('150000.00');

    $offline = User::where('name', 'Nasabah Offline Demo')->first();
    expect($offline)->not->toBeNull()
        ->and($offline->mode_akses)->toBe('offline')
        ->and($offline->no_hp)->toBeNull()
        ->and($offline->isOffline())->toBeTrue()
        ->and($offline->nasabahProfil()->value('catatan_offline'))->toBe('Buku tabungan fisik no. 12');

    $paket = ProdukTabungan::where('nama', 'Paket Sembako Demo')->first();
    expect($paket)->not->toBeNull()
        ->and($paket->tipe)->toBe('paket')
        ->and($paket->isi_paket)->not->toBeEmpty();

    foreach ($paket->isi_paket as $item) {
        expect(data_get($item, 'harga'))->toBeNumeric("Item '{$item['nama']}' wajib punya harga (D15)");
    }

    $kepesertaan = KepesertaanPaket::where('produk_id', $paket->id)->first();
    expect($kepesertaan)->not->toBeNull()
        ->and($kepesertaan->komitmen_disetujui_pada)->not->toBeNull()
        ->and($kepesertaan->komitmen_via)->toBe('mandiri')
        ->and($kepesertaan->komitmen_teks)->not->toBeEmpty()
        ->and((int) $kepesertaan->tunggakan)->toBe(0);

    $penarikan = TransaksiPenarikan::where('status', 'approved')->first();
    expect($penarikan)->not->toBeNull()
        ->and($penarikan->jalur_pengajuan)->toBe('online')
        ->and((float) $penarikan->nominal_diminta)->toBe(50000.0);
});

it('penarikan approved hasil seeder benar-benar bisa dibatalkan dengan pengembalian saldo', function () {
    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    $penarikan = TransaksiPenarikan::where('status', 'approved')->firstOrFail();
    $nasabah = User::findOrFail($penarikan->nasabah_id);
    $saldoSebelum = (float) $nasabah->saldoProduks()
        ->where('produk_id', $penarikan->produk_id)
        ->value('saldo');

    (new BatalkanPenarikanAction)
        ->execute($penarikan, $nasabah, 'uji demo');

    $penarikan->refresh();
    $saldoSesudah = (float) $nasabah->saldoProduks()
        ->where('produk_id', $penarikan->produk_id)
        ->value('saldo');

    expect($penarikan->status)->toBe('dibatalkan')
        ->and($saldoSesudah)->toBe($saldoSebelum + (float) $penarikan->nominal_diminta);
});

it('aman dijalankan dua kali tanpa membuat data ganda', function () {
    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    $jumlah = [
        User::count(),
        ProdukTabungan::count(),
        KepesertaanPaket::count(),
        TransaksiPenarikan::count(),
    ];

    Artisan::call('db:seed', ['--class' => DemoSeeder::class]);

    expect(User::count())->toBe($jumlah[0])
        ->and(ProdukTabungan::count())->toBe($jumlah[1])
        ->and(KepesertaanPaket::count())->toBe($jumlah[2])
        ->and(TransaksiPenarikan::count())->toBe($jumlah[3]);
});

it('tidak membuat data demo di produksi', function () {
    $this->app->detectEnvironment(fn () => 'production');

    Artisan::call('db:seed', ['--class' => DemoSeeder::class, '--force' => true]);

    expect(User::where('name', 'Kolektor Demo')->exists())->toBeFalse()
        ->and(ProdukTabungan::where('nama', 'Paket Sembako Demo')->exists())->toBeFalse();
});
