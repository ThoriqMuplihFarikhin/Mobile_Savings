<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_notifikasi', function (Blueprint $table) {
            $table->string('judul')->after('nasabah_id');
            $table->text('pesan')->after('judul');
            $table->boolean('is_read')->default(false)->after('status_kirim');
        });
    }

    public function down(): void
    {
        Schema::table('log_notifikasi', function (Blueprint $table) {
            $table->dropColumn(['judul', 'pesan', 'is_read']);
        });
    }
};
