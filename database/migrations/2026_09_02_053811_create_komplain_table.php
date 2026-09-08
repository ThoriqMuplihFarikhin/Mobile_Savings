<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komplain', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('users');
            $table->enum('kategori', ['saldo', 'barang_paket', 'penarikan', 'lainnya']);
            $table->foreignId('transaksi_terkait_id')->nullable();
            $table->text('deskripsi');
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users');
            $table->text('catatan_penyelesaian')->nullable();
            $table->timestamp('tanggal_dibuat');
            $table->timestamp('tanggal_selesai')->nullable();
            $table->timestamps();

            $table->index('nasabah_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komplain');
    }
};
