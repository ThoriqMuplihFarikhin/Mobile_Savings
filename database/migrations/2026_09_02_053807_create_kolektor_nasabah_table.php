<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kolektor_nasabah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolektor_id')->constrained('users');
            $table->foreignId('nasabah_id')->constrained('users');
            $table->date('tanggal_mulai_ditangani');
            $table->date('tanggal_selesai_ditangani')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index(['kolektor_id', 'status']);
            $table->index('nasabah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kolektor_nasabah');
    }
};
