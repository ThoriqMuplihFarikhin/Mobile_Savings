<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_penarikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nasabah_id')->constrained('users');
            $table->foreignId('produk_id')->constrained('produk_tabungan');
            $table->decimal('nominal_diminta', 15, 2);
            $table->decimal('persen_komisi_terpakai', 5, 2);
            $table->decimal('nominal_komisi', 15, 2);
            $table->decimal('nominal_diterima', 15, 2);
            $table->enum('jalur_pengajuan', ['online', 'offline']);
            $table->enum('lokasi_pengambilan', ['rumah_kolektor', 'kantor']);
            $table->enum('status', ['pending', 'approved', 'selesai', 'ditolak'])->default('pending');
            $table->foreignId('disetujui_oleh')->nullable()->constrained('users');
            $table->timestamp('waktu_approval')->nullable();
            $table->timestamp('waktu_pencairan')->nullable();
            $table->timestamps();

            $table->index('nasabah_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_penarikan');
    }
};
