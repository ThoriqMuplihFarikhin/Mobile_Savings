<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * FK log_aktivitas.user_id diubah menjadi ON DELETE SET NULL agar
     * penghapusan user tidak menghapus jejak audit (keputusan D5).
     */
    public function up(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('log_aktivitas', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });
    }
};
