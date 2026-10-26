<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class AddKepesertaanIdToTransaksiSetoranTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->foreignId('kepesertaan_id')
                ->nullable()
                ->after('produk_id')
                ->constrained('kepesertaan_paket')
                ->nullOnDelete();
            $table->index('kepesertaan_id');
        });

        $sisa = $this->backfill();
        Log::info("Backfill transaksi_setoran.kepesertaan_id: {$sisa} baris tidak terkait (pasangan punya kepesertaan).");
    }

    /**
     * Kaitkan setoran lama ke kepesertaan pemiliknya.
     *
     * Aturan (sesuai rencana audit, tanpa menebak):
     * - Pasangan nasabah+produk dengan tepat satu kepesertaan: semua setoran
     *   pasangan itu milik kepesertaan itu.
     * - Lebih dari satu: setoran masuk ke kepesertaan terbaru yang created_at
     *   tidak melewati created_at setoran; yang tidak punya kandidat dibiarkan null.
     *
     * Idempoten: hanya menyetel baris yang masih null.
     *
     * @return int Jumlah setoran yang tetap tidak terkait pada pasangan yang punya kepesertaan
     */
    public function backfill(): int
    {
        $pasangan = DB::table('kepesertaan_paket')
            ->select('nasabah_id', 'produk_id')
            ->groupBy('nasabah_id', 'produk_id')
            ->get();

        foreach ($pasangan as $pair) {
            $kepesertaan = DB::table('kepesertaan_paket')
                ->where('nasabah_id', $pair->nasabah_id)
                ->where('produk_id', $pair->produk_id)
                ->orderBy('created_at')
                ->get();

            $setoranTertaut = DB::table('transaksi_setoran')
                ->where('nasabah_id', $pair->nasabah_id)
                ->where('produk_id', $pair->produk_id)
                ->whereNull('kepesertaan_id');

            if ($kepesertaan->count() === 1) {
                $setoranTertaut->update(['kepesertaan_id' => $kepesertaan->first()->id]);

                continue;
            }

            foreach ($setoranTertaut->get() as $setoran) {
                $pemilik = $kepesertaan
                    ->filter(fn ($k) => (string) $k->created_at <= (string) $setoran->created_at)
                    ->last();

                if ($pemilik !== null) {
                    DB::table('transaksi_setoran')
                        ->where('id', $setoran->id)
                        ->update(['kepesertaan_id' => $pemilik->id]);
                }
            }
        }

        return DB::table('transaksi_setoran as s')
            ->whereNull('s.kepesertaan_id')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('kepesertaan_paket as k')
                    ->whereColumn('k.nasabah_id', 's.nasabah_id')
                    ->whereColumn('k.produk_id', 's.produk_id');
            })
            ->count();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_setoran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kepesertaan_id');
        });
    }
}
