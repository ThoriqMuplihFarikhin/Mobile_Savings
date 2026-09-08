<?php

use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KomplainController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\MonitoringAbsensiController;
use App\Http\Controllers\Admin\NasabahController;
use App\Http\Controllers\Admin\PenarikanController as AdminPenarikanController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\RegistrasiController;
use App\Http\Controllers\Admin\RekonsiliasiController;
use App\Http\Controllers\Admin\VerifikasiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kolektor\AbsenController;
use App\Http\Controllers\Kolektor\DaftarNasabahController;
use App\Http\Controllers\Kolektor\JadwalController;
use App\Http\Controllers\Kolektor\NasabahBinaanController;
use App\Http\Controllers\Kolektor\PengaturanController as KolektorPengaturanController;
use App\Http\Controllers\Kolektor\SetoranController;
use App\Http\Controllers\Kolektor\SetorKantorController;
use App\Http\Controllers\Nasabah\NotifikasiController;
use App\Http\Controllers\Nasabah\PenarikanController;
use App\Http\Controllers\Nasabah\PengaturanController as NasabahPengaturanController;
use App\Http\Controllers\Nasabah\ProfilController;
use App\Http\Controllers\Nasabah\RiwayatController;
use App\Http\Controllers\Nasabah\SaldoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/nasabah', [NasabahController::class, 'index'])->name('nasabah.index');
        Route::get('/registrasi', [RegistrasiController::class, 'index'])->name('registrasi.index');
        Route::get('/verifikasi', [VerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/produk', [ProdukController::class, 'index'])->name('produk.index');
        Route::get('/penarikan', [AdminPenarikanController::class, 'index'])->name('penarikan.index');
        Route::get('/rekonsiliasi', [RekonsiliasiController::class, 'index'])->name('rekonsiliasi.index');
        Route::get('/komplain', [KomplainController::class, 'index'])->name('komplain.index');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/log', [LogController::class, 'index'])->name('log.index');
        Route::get('/handover', [HandoverController::class, 'index'])->name('handover.index');
        Route::get('/kolektor', [NasabahController::class, 'kolektor'])->name('kolektor.index');
        Route::get('/bermasalah', [NasabahController::class, 'bermasalah'])->name('bermasalah.index');
        Route::get('/monitoring-setoran', [NasabahController::class, 'monitoringSetoran'])->name('monitoring-setoran.index');
        Route::get('/monitoring-absensi', [MonitoringAbsensiController::class, 'index'])->name('monitoring-absensi.index');
    });

    // Kolektor Routes
    Route::prefix('kolektor')->name('kolektor.')->middleware('role:kolektor')->group(function () {
        Route::get('/setoran', [SetoranController::class, 'index'])->name('setoran.index');
        Route::get('/jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/nasabah', [NasabahBinaanController::class, 'index'])->name('nasabah.index');
        Route::get('/daftar-nasabah', [DaftarNasabahController::class, 'index'])->name('daftar-nasabah.index');
        Route::get('/setor-kantor', [SetorKantorController::class, 'index'])->name('setor-kantor.index');
        Route::get('/penarikan-offline', [SetoranController::class, 'penarikanOffline'])->name('penarikan-offline.index');
        Route::get('/absen', [AbsenController::class, 'index'])->name('absen.index');
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
        Route::get('/profil', [ProfilController::class, 'index'])->name('profil.index');
        Route::get('/pengaturan', [NasabahPengaturanController::class, 'index'])->name('pengaturan.index');
    });
});

require __DIR__.'/settings.php';
