<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_kunjungan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kolektor_id')->constrained('users');
            $table->foreignId('nasabah_id')->constrained('users');
            $table->date('tanggal_jadwal');
            $table->enum('status_kunjungan', ['dikunjungi', 'dilewati', 'tidak_ada'])->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['kolektor_id', 'tanggal_jadwal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_kunjungan');
    }
};
