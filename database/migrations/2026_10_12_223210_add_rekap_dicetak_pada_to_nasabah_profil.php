<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * F5/D30: jejak waktu cetak rekap mutasi per nasabah — dipakai kolom
     * "rekap terakhir dicetak" pada laporan dan sebagai pengingat siklus
     * cetak nasabah offline.
     */
    public function up(): void
    {
        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->timestamp('rekap_dicetak_pada')->nullable()
                ->after('tanggal_verifikasi')
                ->comment('Waktu terakhir rekap mutasi dicetak untuk nasabah ini');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->dropColumn('rekap_dicetak_pada');
        });
    }
};
