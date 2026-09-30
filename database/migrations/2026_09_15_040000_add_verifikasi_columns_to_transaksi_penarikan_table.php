<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->foreignId('diverifikasi_oleh')->nullable()->after('disetujui_oleh')
                ->constrained('users')->comment('Kolektor yang melakukan verifikasi serah terima');
            $table->enum('metode_verifikasi', ['pin_nasabah', 'manual_admin'])->nullable()->after('diverifikasi_oleh');
            $table->unsignedTinyInteger('percobaan_verifikasi_gagal')->default(0)->after('metode_verifikasi');
            $table->timestamp('terkunci_hingga')->nullable()->after('percobaan_verifikasi_gagal')
                ->comment('Verifikasi PIN dikunci sementara jika gagal berulang');
        });
    }

    public function down(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('diverifikasi_oleh');
            $table->dropColumn(['metode_verifikasi', 'percobaan_verifikasi_gagal', 'terkunci_hingga']);
        });
    }
};
