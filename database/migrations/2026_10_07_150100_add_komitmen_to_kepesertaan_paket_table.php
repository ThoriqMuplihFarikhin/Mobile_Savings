<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddKomitmenToKepesertaanPaketTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kepesertaan_paket', function (Blueprint $table) {
            $table->timestamp('komitmen_disetujui_pada')->nullable()->after('ditunda_hingga');
            $table->enum('komitmen_via', ['mandiri', 'kolektor', 'admin', 'migrasi'])->nullable()->after('komitmen_disetujui_pada');
            $table->foreignId('komitmen_dicatat_oleh')->nullable()->after('komitmen_via')->constrained('users')->nullOnDelete();
            $table->text('komitmen_teks')->nullable()->after('komitmen_dicatat_oleh');
            $table->string('komitmen_catatan')->nullable()->after('komitmen_teks');
        });

        $jumlah = $this->backfill();
        Log::info("Backfill kepesertaan_paket komitmen: {$jumlah} baris tidak terisi (dilewati, harusnya 0).");
    }

    /**
     * Tandai kepesertaan lama sebagai komitmen hasil migrasi, disetujui saat
     * baris dibuat. Idempoten: hanya menyetel baris yang masih null.
     *
     * @return int Jumlah kepesertaan yang tetap tanpa komitmen_via
     */
    public function backfill(): int
    {
        DB::table('kepesertaan_paket')
            ->whereNull('komitmen_via')
            ->update([
                'komitmen_via' => 'migrasi',
                'komitmen_disetujui_pada' => DB::raw('created_at'),
                'updated_at' => now(),
            ]);

        return DB::table('kepesertaan_paket')->whereNull('komitmen_via')->count();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kepesertaan_paket', function (Blueprint $table) {
            $table->dropConstrainedForeignId('komitmen_dicatat_oleh');
            $table->dropColumn(['komitmen_disetujui_pada', 'komitmen_via', 'komitmen_teks', 'komitmen_catatan']);
        });
    }
}
