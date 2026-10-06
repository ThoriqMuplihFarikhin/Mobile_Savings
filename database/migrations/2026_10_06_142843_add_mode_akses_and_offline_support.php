<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * D14: skema mode offline — mode_akses, no_hp nullable,
     * catatan_offline profil, jalur/metode verifikasi offline.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('mode_akses', ['digital', 'offline'])->default('digital')
                ->after('status_akun')
                ->comment('Digital = punya HP; Offline = didaftarkan tanpa aplikasi (D14)');
            $table->index('mode_akses');
            $table->string('no_hp', 20)->nullable()->change();
        });

        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->text('catatan_offline')->nullable()
                ->after('pekerjaan')
                ->comment('Catatan/kendala nasabah offline (D14)');
        });

        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->enum('jalur_pengajuan', ['online', 'offline', 'offline_kolektor'])->change();
            $table->enum('metode_verifikasi', ['pin_nasabah', 'manual_admin', 'tanpa_verifikasi_offline'])
                ->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->enum('metode_verifikasi', ['pin_nasabah', 'manual_admin'])->nullable()->change();
            $table->enum('jalur_pengajuan', ['online', 'offline'])->change();
        });

        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->dropColumn('catatan_offline');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 20)->nullable(false)->change();
            $table->dropIndex(['mode_akses']);
            $table->dropColumn('mode_akses');
        });
    }
};
