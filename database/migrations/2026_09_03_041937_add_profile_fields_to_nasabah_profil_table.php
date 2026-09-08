<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->date('tanggal_lahir')->nullable()->after('alamat');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->default('laki-laki')->after('tanggal_lahir');
            $table->string('pekerjaan')->nullable()->after('jenis_kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nasabah_profil', function (Blueprint $table) {
            $table->dropColumn(['tanggal_lahir', 'jenis_kelamin', 'pekerjaan']);
        });
    }
};
