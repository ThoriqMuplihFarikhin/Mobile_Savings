<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('no_hp', 20)->unique()->after('name');
            $table->string('pin_hash')->after('no_hp');
            $table->enum('role', ['nasabah', 'kolektor', 'admin'])->default('nasabah')->after('pin_hash');
            $table->enum('status_akun', ['aktif', 'terkunci'])->default('aktif')->after('role');
            $table->integer('percobaan_gagal')->default(0)->after('status_akun');
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'pin_hash', 'role', 'status_akun', 'percobaan_gagal']);
            $table->string('email')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
