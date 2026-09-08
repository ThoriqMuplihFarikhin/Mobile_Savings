<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_handover_kolektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolektor_lama_id')->constrained('users');
            $table->foreignId('kolektor_baru_id')->constrained('users');
            $table->date('tanggal_handover');
            $table->integer('jumlah_nasabah_dipindah');
            $table->enum('status_kas_saat_handover', ['lunas', 'masih_tunggakan']);
            $table->foreignId('diproses_oleh')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_handover_kolektor');
    }
};
