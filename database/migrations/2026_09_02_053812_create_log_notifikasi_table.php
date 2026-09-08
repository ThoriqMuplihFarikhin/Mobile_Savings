<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('users');
            $table->string('jenis_notifikasi');
            $table->enum('channel', ['whatsapp', 'in_app']);
            $table->enum('status_kirim', ['terkirim', 'gagal']);
            $table->timestamp('waktu_kirim');
            $table->timestamps();

            $table->index('nasabah_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_notifikasi');
    }
};
