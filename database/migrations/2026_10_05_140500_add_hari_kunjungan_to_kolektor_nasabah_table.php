<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kolektor_nasabah', function (Blueprint $table) {
            $table->json('hari_kunjungan')->nullable()->after('aktif_unik');
        });
    }

    public function down(): void
    {
        Schema::table('kolektor_nasabah', function (Blueprint $table) {
            $table->dropColumn('hari_kunjungan');
        });
    }
};
