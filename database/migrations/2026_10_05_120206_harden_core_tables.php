<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->index('tanggal_transaksi');
            $table->index(['input_by', 'status', 'sudah_disetor_ke_kantor']);
        });

        $this->netralkanSetoranKolektorYatim();

        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->index('setoran_kolektor_id');
            $table->foreign('setoran_kolektor_id')
                ->references('id')
                ->on('setoran_kolektor_kantor')
                ->nullOnDelete();
        });

        $this->tambahConstraintCheck();
    }

    public function down(): void
    {
        $this->hapusConstraintCheck();

        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->dropForeign(['setoran_kolektor_id']);
            $table->dropIndex(['setoran_kolektor_id']);
        });

        // FK `input_by` menumpang pada index komposit (index auto FK asli lenyap
        // saat komposit dibuat) — lepas FK-nya dulu agar komposit bisa dibuang,
        // lalu pasang kembali agar MySQL membuat index pendukungnya lagi.
        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->dropForeign(['input_by']);
        });

        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->dropIndex(['input_by', 'status', 'sudah_disetor_ke_kantor']);
            $table->dropIndex(['tanggal_transaksi']);
        });

        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->foreign('input_by')->references('id')->on('users');
        });
    }

    /**
     * Menetralkan `transaksi_setoran.setoran_kolektor_id` yang menunjuk baris
     * `setoran_kolektor_kantor` yang sudah tidak ada, agar penambahan foreign key tidak gagal.
     */
    private function netralkanSetoranKolektorYatim(): void
    {
        $jumlahYatim = DB::table('transaksi_setoran')
            ->whereNotNull('setoran_kolektor_id')
            ->whereNotIn('setoran_kolektor_id', DB::table('setoran_kolektor_kantor')->select('id'))
            ->count();

        if ($jumlahYatim === 0) {
            return;
        }

        DB::table('transaksi_setoran')
            ->whereNotNull('setoran_kolektor_id')
            ->whereNotIn('setoran_kolektor_id', DB::table('setoran_kolektor_kantor')->select('id'))
            ->update(['setoran_kolektor_id' => null]);

        report(new RuntimeException(
            "Migrasi harden_core_tables menemukan {$jumlahYatim} baris transaksi_setoran dengan setoran_kolektor_id yatim; nilai dinetralkan ke NULL."
        ));
    }

    private function tambahConstraintCheck(): void
    {
        if (! $this->dukungConstraintCheck()) {
            return;
        }

        DB::statement('ALTER TABLE saldo_produk ADD CONSTRAINT chk_saldo_produk_nonnegative CHECK (saldo >= 0)');
        DB::statement('ALTER TABLE transaksi_setoran ADD CONSTRAINT chk_transaksi_setoran_nominal_positive CHECK (nominal > 0)');
        DB::statement('ALTER TABLE transaksi_penarikan ADD CONSTRAINT chk_transaksi_penarikan_nominal_diminta_positive CHECK (nominal_diminta > 0)');
    }

    private function hapusConstraintCheck(): void
    {
        if (! $this->dukungConstraintCheck()) {
            return;
        }

        DB::statement('ALTER TABLE saldo_produk DROP CHECK chk_saldo_produk_nonnegative');
        DB::statement('ALTER TABLE transaksi_setoran DROP CHECK chk_transaksi_setoran_nominal_positive');
        DB::statement('ALTER TABLE transaksi_penarikan DROP CHECK chk_transaksi_penarikan_nominal_diminta_positive');
    }

    /**
     * CHECK constraint baru didukung MySQL 8.0.16 ke atas; driver lain dilewati.
     */
    private function dukungConstraintCheck(): bool
    {
        $koneksi = DB::connection();

        return $koneksi->getDriverName() === 'mysql'
            && version_compare($koneksi->getServerVersion(), '8.0.16', '>=');
    }
};
