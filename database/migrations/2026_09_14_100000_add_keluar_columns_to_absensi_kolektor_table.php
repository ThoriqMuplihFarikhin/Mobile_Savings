<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->time('waktu_keluar')->nullable()->after('tanda_tangan_path');
            $table->decimal('latitude_keluar', 10, 7)->nullable()->after('waktu_keluar');
            $table->decimal('longitude_keluar', 10, 7)->nullable()->after('latitude_keluar');
            $table->string('foto_selfie_keluar_path')->nullable()->after('longitude_keluar');
            $table->string('tanda_tangan_keluar_path')->nullable()->after('foto_selfie_keluar_path');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->dropColumn([
                'waktu_keluar',
                'latitude_keluar',
                'longitude_keluar',
                'foto_selfie_keluar_path',
                'tanda_tangan_keluar_path',
            ]);
        });
    }
};
