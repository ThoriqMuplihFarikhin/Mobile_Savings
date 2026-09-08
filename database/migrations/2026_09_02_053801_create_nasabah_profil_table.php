<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nasabah_profil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->text('alamat');
            $table->foreignId('didaftarkan_oleh')->constrained('users');
            $table->enum('status_pendaftaran', ['pending_verifikasi', 'aktif', 'ditolak'])->default('pending_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->timestamps();

            $table->index('status_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nasabah_profil');
    }
};
