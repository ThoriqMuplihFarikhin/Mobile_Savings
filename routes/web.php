<?php

use App\Http\Controllers\AbsensiFotoController;
use App\Http\Controllers\Admin\DetailNasabahController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\InputSetoranController;
use App\Http\Controllers\Admin\KasKolektorController;
use App\Http\Controllers\Admin\KomisiController;
use App\Http\Controllers\Admin\KomplainController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MonitoringAbsensiController;
use App\Http\Controllers\Admin\NasabahController;
use App\Http\Controllers\Admin\PenarikanController as AdminPenarikanController;
use App\Http\Controllers\Admin\PengaturanController as AdminPengaturanController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\RegistrasiController;
use App\Http\Controllers\Admin\RekonsiliasiController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kolektor\AbsenController;
use App\Http\Controllers\Kolektor\DaftarNasabahController;
use App\Http\Controllers\Kolektor\JadwalController;
use App\Http\Controllers\Kolektor\NasabahBinaanController;
use App\Http\Controllers\Kolektor\PengaturanController as KolektorPengaturanController;
use App\Http\Controllers\Kolektor\RiwayatAbsensiController;
use App\Http\Controllers\Kolektor\SetoranController;
use App\Http\Controllers\Kolektor\SetorKantorController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Nasabah\NotifikasiController;
use App\Http\Controllers\Nasabah\PenarikanController;
use App\Http\Controllers\Nasabah\PengaturanController as NasabahPengaturanController;
use App\Http\Controllers\Nasabah\RiwayatController;
use App\Http\Controllers\Nasabah\SaldoController;
use App\Http\Controllers\RekapMutasiController;
use App\Http\Controllers\SerahTerimaFotoController;
use App\Http\Controllers\SerahTerimaPaketController;
use App\Http\Controllers\StrukSetoranController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/absensi/{absensi}/{jenis}', [AbsensiFotoController::class, 'show'])
        ->where('jenis', 'selfie|tanda-tangan')
        ->name('absensi.foto');

    Route::get('/serah-terima/{kepesertaan}/bukti', [SerahTerimaFotoController::class, 'show'])
        ->name('serah-terima.bukti');

    Route::get('/serah-terima-paket', [SerahTerimaPaketController::class, 'index'])
        ->middleware('role:admin|kolektor')
        ->name('serah-terima.index');

    Route::get('/struk/setoran/{setoran}', [StrukSetoranController::class, 'show'])
        ->name('struk.setoran');

    Route::get('/rekap/nasabah/{user}', [RekapMutasiController::class, 'show'])
        ->middleware('role:admin|kolektor')
        ->name('rekap.nasabah');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/nasabah', [NasabahController::class, 'index'])->name('nasabah.index');
        Route::get('/nasabah/{user}', [DetailNasabahController::class, 'index'])->name('nasabah.detail');
        Route::get('/registrasi', [RegistrasiController::class, 'index'])->name('registrasi.index');
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/penarikan', [AdminPenarikanController::class, 'index'])->name('penarikan.index');
        Route::get('/komisi', [KomisiController::class, 'index'])->name('komisi.index');
        Route::get('/rekonsiliasi', [RekonsiliasiController::class, 'index'])->name('rekonsiliasi.index');
        Route::get('/kas-kolektor', [KasKolektorController::class, 'index'])->name('kas-kolektor.index');
        Route::get('/komplain', [KomplainController::class, 'index'])->name('komplain.index');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/log', [LogController::class, 'index'])->name('log.index');
        Route::get('/handover', [HandoverController::class, 'index'])->name('handover.index');
        Route::get('/kolektor', [NasabahController::class, 'kolektor'])->name('kolektor.index');
        Route::get('/bermasalah', [NasabahController::class, 'bermasalah'])->name('bermasalah.index');
        Route::get('/monitoring-setoran', [NasabahController::class, 'monitoringSetoran'])->name('monitoring-setoran.index');
        Route::get('/setoran', [InputSetoranController::class, 'index'])->name('setoran.create');
        Route::get('/monitoring-absensi', [MonitoringAbsensiController::class, 'index'])->name('monitoring-absensi.index');
        Route::get('/pengaturan', [AdminPengaturanController::class, 'index'])->name('pengaturan.index');

        // Settings khusus admin
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/profile', [AdminSettingsController::class, 'profile'])->name('profile');
            Route::get('/security', [AdminSettingsController::class, 'security'])->name('security');
            Route::get('/appearance', [AdminSettingsController::class, 'appearance'])->name('appearance');
        });
    });

    // Kolektor Routes
    Route::prefix('kolektor')->name('kolektor.')->middleware('role:kolektor')->group(function () {
        Route::get('/setoran', [SetoranController::class, 'index'])->name('setoran.index');
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/nasabah', [NasabahBinaanController::class, 'index'])->name('nasabah.index');
        Route::get('/daftar-nasabah', [DaftarNasabahController::class, 'index'])->name('daftar-nasabah.index');
        Route::get('/setor-kantor', [SetorKantorController::class, 'index'])->name('setor-kantor.index');
        Route::get('/penarikan-offline', [SetoranController::class, 'penarikanOffline'])->name('penarikan-offline.index');
        Route::get('/verifikasi-penarikan', [SetoranController::class, 'verifikasiPenarikan'])->name('verifikasi-penarikan.index');
        Route::get('/absen', [AbsenController::class, 'index'])->name('absen.index');
        Route::get('/izin', fn () => view('pages.kolektor.izin'))->name('izin.index');
        Route::get('/riwayat-absensi', [RiwayatAbsensiController::class, 'index'])->name('riwayat-absensi.index');
        Route::get('/pengaturan', [KolektorPengaturanController::class, 'index'])->name('pengaturan.index');
    });

    // Nasabah Routes
    Route::prefix('nasabah')->name('nasabah.')->middleware('role:nasabah')->group(function () {
        Route::get('/penarikan', [PenarikanController::class, 'index'])->name('penarikan.index');
        Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
        Route::get('/riwayat-tabungan', [RiwayatController::class, 'riwayatTabungan'])->name('riwayat-tabungan.index');
        Route::get('/progres-paket', [RiwayatController::class, 'progresPaket'])->name('progres-paket.index');
        Route::get('/komplain', [RiwayatController::class, 'komplain'])->name('komplain.index');
        Route::get('/saldo', [SaldoController::class, 'index'])->name('saldo.index');
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::get('/pengaturan', [NasabahPengaturanController::class, 'index'])->name('pengaturan.index');
    });
});

require __DIR__.'/settings.php';
