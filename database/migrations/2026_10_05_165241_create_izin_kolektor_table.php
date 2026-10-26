<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izin_kolektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolektor_id')->constrained('users');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('alasan', 500);
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->foreignId('diproses_oleh')->nullable()->constrained('users');
            $table->string('catatan_admin', 500)->nullable();
            $table->timestamps();
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_kolektor');
    }
};
