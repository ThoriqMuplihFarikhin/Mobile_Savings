<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kolektor_nasabah', function (Blueprint $table) {
            $table->tinyInteger('aktif_unik')->nullable()->after('status');
        });

        DB::table('kolektor_nasabah')->where('status', 'aktif')->update(['aktif_unik' => 1]);

        Schema::table('kolektor_nasabah', function (Blueprint $table) {
            $table->unique(['nasabah_id', 'aktif_unik']);
        });
    }

    public function down(): void
    {
        Schema::table('kolektor_nasabah', function (Blueprint $table) {
            $table->dropUnique(['nasabah_id', 'aktif_unik']);
            $table->dropColumn('aktif_unik');
        });
    }
};
