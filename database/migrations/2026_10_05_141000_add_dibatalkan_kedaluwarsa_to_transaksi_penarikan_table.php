<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'selesai', 'ditolak', 'dibatalkan', 'kedaluwarsa'])
                ->default('pending')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'selesai', 'ditolak'])
                ->default('pending')
                ->change();
        });
    }
};
