<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->string('tanda_tangan_path')->nullable()->after('foto_selfie_path');
        });

        $absensi = DB::table('absensi_kolektor')
            ->whereNotNull('tanda_tangan_base64')
            ->get();

        foreach ($absensi as $item) {
            $base64 = $item->tanda_tangan_base64;
            $base64 = str_replace('data:image/png;base64,', '', $base64);
            $base64 = str_replace('data:image/jpeg;base64,', '', $base64);
            $data = base64_decode($base64);

            $filename = 'tanda_tangan/'.$item->kolektor_id.'_'.$item->id.'.png';
            Storage::disk('public')->put($filename, $data);

            DB::table('absensi_kolektor')
                ->where('id', $item->id)
                ->update(['tanda_tangan_path' => $filename]);
        }

        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_base64');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->text('tanda_tangan_base64')->nullable()->after('foto_selfie_path');
        });

        $absensi = DB::table('absensi_kolektor')
            ->whereNotNull('tanda_tangan_path')
            ->get();

        foreach ($absensi as $item) {
            if (Storage::disk('public')->exists($item->tanda_tangan_path)) {
                $data = Storage::disk('public')->get($item->tanda_tangan_path);
                $base64 = base64_encode($data);
                $base64 = 'data:image/png;base64,'.$base64;

                DB::table('absensi_kolektor')
                    ->where('id', $item->id)
                    ->update(['tanda_tangan_base64' => $base64]);

                Storage::disk('public')->delete($item->tanda_tangan_path);
            }
        }

        Schema::table('absensi_kolektor', function (Blueprint $table) {
            $table->dropColumn('tanda_tangan_path');
        });
    }
};
