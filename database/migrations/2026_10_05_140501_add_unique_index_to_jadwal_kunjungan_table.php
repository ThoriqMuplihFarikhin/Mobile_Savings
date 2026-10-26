<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->hapusJadwalGanda();

        Schema::table('jadwal_kunjungan', function (Blueprint $table) {
            $table->unique(['kolektor_id', 'nasabah_id', 'tanggal_jadwal']);
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_kunjungan', function (Blueprint $table) {
            $table->dropUnique(['kolektor_id', 'nasabah_id', 'tanggal_jadwal']);
        });
    }

    /**
     * Menghapus jadwal ganda per (kolektor, nasabah, tanggal) sebelum unique index dibuat,
     * mempertahankan baris tertua (id terkecil).
     */
    private function hapusJadwalGanda(): void
    {
        $ganda = DB::table('jadwal_kunjungan')
            ->select('kolektor_id', 'nasabah_id', 'tanggal_jadwal', DB::raw('MIN(id) AS id_terlama'))
            ->groupBy('kolektor_id', 'nasabah_id', 'tanggal_jadwal')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        if ($ganda->isEmpty()) {
            return;
        }

        foreach ($ganda as $kunci) {
            DB::table('jadwal_kunjungan')
                ->where('kolektor_id', $kunci->kolektor_id)
                ->where('nasabah_id', $kunci->nasabah_id)
                ->where('tanggal_jadwal', $kunci->tanggal_jadwal)
                ->where('id', '!=', $kunci->id_terlama)
                ->delete();
        }

        report(new RuntimeException(
            "Migrasi unique index jadwal_kunjungan menemukan {$ganda->count()} kombinasi jadwal ganda; baris duplikat lebih baru dihapus."
        ));
    }
};
