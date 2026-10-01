<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->decimal('akurasi', 10, 2)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->dropColumn('akurasi');
        });
    }
};
