<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kepesertaan_paket', function (Blueprint $table) {
            $table->date('ditunda_hingga')->nullable()->after('catatan_admin');
        });

        $jumlah = DB::table('kepesertaan_paket')
            ->where('keputusan_akhir', 'lanjut')
            ->update([
                'keputusan_akhir' => null,
                'ditunda_hingga' => now()->addDays(30)->toDateString(),
                'updated_at' => now(),
            ]);

        Log::info("Migrasi D1: {$jumlah} kepesertaan berstatus 'lanjut' diubah jadi ditunda_hingga +30 hari tanpa keputusan_akhir.");
    }

    public function down(): void
    {
        Schema::table('kepesertaan_paket', function (Blueprint $table) {
            $table->dropColumn('ditunda_hingga');
        });
    }
};
