<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_setoran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('users');
            $table->foreignId('produk_id')->constrained('produk_tabungan');
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_transaksi');
            $table->timestamp('tanggal_input_sistem');
            $table->foreignId('input_by')->constrained('users');
            $table->enum('sumber_input', ['real_time', 'susulan']);
            $table->enum('status', ['tercatat', 'dikoreksi', 'dibatalkan'])->default('tercatat');
            $table->decimal('nominal_asli', 15, 2)->nullable();
            $table->foreignId('dikoreksi_oleh')->nullable()->constrained('users');
            $table->text('alasan_koreksi')->nullable();
            $table->boolean('sudah_disetor_ke_kantor')->default(false);
            $table->unsignedBigInteger('setoran_kolektor_id')->nullable();
            $table->timestamps();

            $table->index('nasabah_id');
            $table->index('produk_id');
            $table->index('status');
            $table->index('sudah_disetor_ke_kantor');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_setoran');
    }
};
