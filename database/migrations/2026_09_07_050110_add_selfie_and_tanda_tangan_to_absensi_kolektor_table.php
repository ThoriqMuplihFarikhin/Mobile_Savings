<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->string('foto_selfie_path')->nullable()->after('longitude');
            $table->text('tanda_tangan_base64')->nullable()->after('foto_selfie_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->dropColumn(['foto_selfie_path', 'tanda_tangan_base64']);
        });
    }
};
