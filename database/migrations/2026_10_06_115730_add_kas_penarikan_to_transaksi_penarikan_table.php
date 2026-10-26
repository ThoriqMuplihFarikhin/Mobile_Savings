<?php

use App\Models\TransaksiPenarikan;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema D13 (kas kolektor) + D18 (pembatalan penarikan) dalam satu migrasi.
     */
    public function up(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->foreignId('dibayar_oleh')->nullable()
                ->comment('Kolektor yang membayar tunai penarikan (D13)')
                ->constrained('users');
            $table->boolean('mempengaruhi_kas')->default(false)
                ->comment('Penarikan tunai ini mengurangi kas kolektor (D13)');
            $table->foreignId('setoran_kolektor_id')->nullable()
                ->comment('Pengajuan setor kantor yang menautkan penarikan ini (D13)')
                ->constrained('setoran_kolektor_kantor')->nullOnDelete();
            $table->foreignId('dibatalkan_oleh')->nullable()
                ->comment('Pelaku pembatalan penarikan (D18)')
                ->constrained('users');
            $table->string('alasan_batal', 255)->nullable();
            $table->timestamp('waktu_dibatalkan')->nullable();
        });

        TransaksiPenarikan::backfillHistorisKas();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dibatalkan_oleh');
            $table->dropConstrainedForeignId('setoran_kolektor_id');
            $table->dropConstrainedForeignId('dibayar_oleh');
            $table->dropColumn(['mempengaruhi_kas', 'alasan_batal', 'waktu_dibatalkan']);
        });
    }
};
