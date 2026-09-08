<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kepesertaan_paket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('users');
            $table->foreignId('produk_id')->constrained('produk_tabungan');
            $table->date('tanggal_mulai_ikut');
            $table->decimal('total_seharusnya_terkumpul', 15, 2)->default(0);
            $table->decimal('total_aktual_terkumpul', 15, 2)->default(0);
            $table->decimal('tunggakan', 15, 2)->default(0);
            $table->enum('status_alert', ['normal', 'peringatan', 'perlu_review'])->default('normal');
            $table->text('catatan_admin')->nullable();
            $table->enum('keputusan_akhir', ['lanjut', 'gagal_dikembalikan', 'gagal_dialihkan'])->nullable();
            $table->enum('metode_pengambilan', ['ambil_sendiri', 'diantar_kolektor'])->nullable();
            $table->enum('status_serah_terima', ['belum', 'sudah_diterima'])->default('belum');
            $table->string('diterima_oleh')->nullable();
            $table->date('tanggal_serah_terima')->nullable();
            $table->string('bukti_foto_url')->nullable();
            $table->timestamps();

            $table->index('nasabah_id');
            $table->index('status_alert');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kepesertaan_paket');
    }
};
