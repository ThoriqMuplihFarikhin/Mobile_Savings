<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk_tabungan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('tipe', ['bebas', 'paket']);
            $table->decimal('persen_komisi', 5, 2);
            $table->decimal('minimal_setor', 15, 2)->nullable();
            $table->decimal('harga_per_hari', 15, 2)->nullable();
            $table->json('isi_paket')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->date('tanggal_boleh_cair')->nullable();
            $table->integer('batas_toleransi_tunggakan_hari')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index('tipe');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk_tabungan');
    }
};
