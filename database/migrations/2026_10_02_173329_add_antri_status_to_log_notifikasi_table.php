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
        Schema::table('log_notifikasi', function (Blueprint $table) {
            $table->enum('status_kirim', ['antri', 'terkirim', 'gagal'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_notifikasi', function (Blueprint $table) {
            $table->enum('status_kirim', ['terkirim', 'gagal'])->change();
        });
    }
};
