<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produk_tabungan', function (Blueprint $table) {
            $table->boolean('tampilkan_harga_ke_nasabah')->default(false)->after('isi_paket');
            $table->boolean('boleh_cair_saat_target')->default(false)->after('tanggal_boleh_cair');
            $table->date('batas_daftar_hingga')->nullable()->after('boleh_cair_saat_target');
        });
    }

    public function down(): void
    {
        Schema::table('produk_tabungan', function (Blueprint $table) {
            $table->dropColumn(['tampilkan_harga_ke_nasabah', 'boleh_cair_saat_target', 'batas_daftar_hingga']);
        });
    }
};
