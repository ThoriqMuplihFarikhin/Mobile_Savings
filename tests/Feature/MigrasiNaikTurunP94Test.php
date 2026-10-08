<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * @return array<int, string>
 */
function p94DaftarIndeksScratch(): array
{
    return array_map(
        fn ($baris) => (string) $baris->INDEX_NAME,
        DB::connection('p94_scratch')->select(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() GROUP BY INDEX_NAME'
        )
    );
}

/**
 * @return list<string>
 */
function p94DaftarFkScratch(): array
{
    return array_map(
        fn ($baris) => (string) $baris->CONSTRAINT_NAME,
        DB::connection('p94_scratch')->select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL GROUP BY CONSTRAINT_NAME'
        )
    );
}

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'mysql') {
        $this->markTestSkipped('Siklus migrasi diuji pada MySQL.');
    }

    $mysql = config('database.connections.mysql');

    config()->set('database.connections.p94_server', array_merge($mysql, ['database' => 'information_schema']));
    config()->set('database.connections.p94_scratch', array_merge($mysql, ['database' => 'tabungan_digital_migrasi_test']));

    DB::connection('p94_server')->statement(
        'CREATE DATABASE IF NOT EXISTS tabungan_digital_migrasi_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    config()->set('database.default', 'p94_scratch');
    DB::purge('p94_scratch');
});

afterEach(function () {
    config()->set('database.default', 'mysql');
    DB::purge('p94_scratch');
    DB::connection('p94_server')->statement('DROP DATABASE IF EXISTS tabungan_digital_migrasi_test');
});

it('menjalankan seluruh migrasi naik, turun, lalu naik kembali tanpa error', function () {
    $naikPertama = Artisan::call('migrate');
    expect($naikPertama)->toBe(0);

    $turun = Artisan::call('migrate:rollback');
    expect($turun)->toBe(0);

    $tabel = DB::connection('p94_scratch')->select(
        "SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'transaksi_setoran'"
    );
    expect($tabel)->toBeEmpty();

    $naikKedua = Artisan::call('migrate');
    expect($naikKedua)->toBe(0);

    $indeks = p94DaftarIndeksScratch();
    $fk = p94DaftarFkScratch();
    expect($indeks)->toContain('transaksi_setoran_input_by_status_sudah_disetor_ke_kantor_index')
        ->and($indeks)->toContain('transaksi_setoran_tanggal_transaksi_index')
        ->and($fk)->toContain('transaksi_setoran_setoran_kolektor_id_foreign');
});
