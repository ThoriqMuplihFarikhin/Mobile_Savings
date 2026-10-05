<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->foreignId('disetujui_oleh_2')->nullable()
                ->after('disetujui_oleh')
                ->comment('Admin kedua pada persetujuan ganda (D9)')
                ->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disetujui_oleh_2');
        });
    }
};
