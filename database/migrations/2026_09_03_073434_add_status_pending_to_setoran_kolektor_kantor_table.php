<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('setoran_kolektor_kantor', function (Blueprint $table) {
            $table->enum('status', ['pending', 'cocok', 'lebih', 'kurang'])->default('pending')->change();
            $table->decimal('total_diterima', 15, 2)->nullable()->change();
            $table->decimal('selisih', 15, 2)->nullable()->change();
            $table->foreignId('diterima_oleh')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('setoran_kolektor_kantor', function (Blueprint $table) {
            $table->enum('status', ['cocok', 'lebih', 'kurang'])->change();
            $table->decimal('total_diterima', 15, 2)->nullable(false)->change();
            $table->decimal('selisih', 15, 2)->nullable(false)->change();
            $table->foreignId('diterima_oleh')->nullable(false)->change();
        });
    }
};
