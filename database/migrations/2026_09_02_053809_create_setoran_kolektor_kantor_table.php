<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('setoran_kolektor_kantor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolektor_id')->constrained('users');
            $table->date('tanggal_setor');
            $table->decimal('total_seharusnya', 15, 2);
            $table->decimal('total_diterima', 15, 2)->nullable();
            $table->decimal('selisih', 15, 2)->nullable();
            $table->text('keterangan_selisih')->nullable();
            $table->foreignId('diterima_oleh')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'cocok', 'lebih', 'kurang'])->default('pending');
            $table->timestamps();

            $table->index('kolektor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setoran_kolektor_kantor');
    }
};
