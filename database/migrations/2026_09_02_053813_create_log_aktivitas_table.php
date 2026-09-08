<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('aksi');
            $table->string('entitas_terkait');
            $table->unsignedBigInteger('entitas_id');
            $table->json('detail')->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();

            $table->index(['entitas_terkait', 'entitas_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
