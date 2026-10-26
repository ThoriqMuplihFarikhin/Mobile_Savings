<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->string('bukti_path', 191)->nullable()->after('metode_verifikasi')
                ->comment('Berkas bukti penarikan offline pada disk privat local (D24)');
            $table->enum('bukti_jenis', ['tanda_tangan', 'foto'])->nullable()->after('bukti_path');
            $table->text('catatan_kolektor')->nullable()->after('bukti_jenis')
                ->comment('Catatan wajib kolektor, disimpan di baris penarikan bukan hanya di log (D24)');
            $table->foreignId('ditinjau_oleh')->nullable()->after('catatan_kolektor')
                ->comment('Admin yang meninjau penarikan offline langsung (D25)')
                ->constrained('users');
            $table->timestamp('ditinjau_pada')->nullable()->after('ditinjau_oleh');
            $table->enum('hasil_tinjauan', ['sesuai', 'bermasalah'])->nullable()->after('ditinjau_pada');
            $table->string('catatan_tinjauan', 255)->nullable()->after('hasil_tinjauan');
            $table->index(['metode_verifikasi', 'hasil_tinjauan']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('izin_penarikan_langsung')->default(true)
                ->after('harus_ganti_pin')
                ->comment('Kolektor dibekukan dari penarikan offline langsung bila false (D23)');
        });

        /*
         * Backfill (idempoten, D23): penarikan offline langsung lama sengaja
         * dibiarkan `hasil_tinjauan = NULL` (default kolom baru) sehingga seluruh
         * riwayat langsung tampil sebagai antrian "belum ditinjau" di halaman
         * tinjauan — backlog harus terlihat admin, bukan dianggap sudah selesai.
         * Karena NULL adalah nilai awal kolom, pemanggilan ulang = 0 baris berubah.
         */
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('izin_penarikan_langsung');
        });

        Schema::table('transaksi_penarikan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('ditinjau_oleh');
            $table->dropIndex(['metode_verifikasi', 'hasil_tinjauan']);
            $table->dropColumn([
                'bukti_path',
                'bukti_jenis',
                'catatan_kolektor',
                'ditinjau_pada',
                'hasil_tinjauan',
                'catatan_tinjauan',
            ]);
        });
    }
};
