<?php

namespace App\Console\Commands;

use App\Models\JadwalKunjungan;
use App\Models\KolektorNasabah;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('jadwal:generate')]
#[Description('Buat baris jadwal_kunjungan (status belum) dari hari_kunjungan tiap binaan aktif')]
class GenerateJadwalKunjungan extends Command
{
    public function handle(): int
    {
        $tanggal = now()->toDateString();
        $hari = (int) now()->isoWeekday();

        $dibuat = 0;

        $binaan = KolektorNasabah::where('status', 'aktif')
            ->whereNotNull('hari_kunjungan')
            ->get();

        foreach ($binaan as $pasangan) {
            $hariKunjungan = array_map('intval', $pasangan->hari_kunjungan ?? []);

            if (! in_array($hari, $hariKunjungan, true)) {
                continue;
            }

            $jadwal = JadwalKunjungan::updateOrCreate(
                [
                    'kolektor_id' => $pasangan->kolektor_id,
                    'nasabah_id' => $pasangan->nasabah_id,
                    'tanggal_jadwal' => $tanggal,
                ],
                []
            );

            if ($jadwal->wasRecentlyCreated) {
                $dibuat++;
            }
        }

        $this->info("Jadwal kunjungan dibuat: {$dibuat} baris untuk {$tanggal}.");

        return self::SUCCESS;
    }
}
