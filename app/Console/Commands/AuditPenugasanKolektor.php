<?php

namespace App\Console\Commands;

use App\Models\KolektorNasabah;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('kolektor:audit-penugasan')]
#[Description('Audit penugasan kolektor: duplikat nasabah aktif dan penugasan ke akun non-kolektor')]
class AuditPenugasanKolektor extends Command
{
    public function handle(): int
    {
        $duplikat = KolektorNasabah::query()
            ->where('status', 'aktif')
            ->groupBy('nasabah_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('nasabah_id')
            ->values();

        $keNonKolektor = KolektorNasabah::query()
            ->where('status', 'aktif')
            ->join('users', 'users.id', '=', 'kolektor_nasabah.kolektor_id')
            ->where('users.role', '!=', 'kolektor')
            ->select('kolektor_nasabah.*', 'users.role as peran_kolektor', 'users.name as nama_kolektor')
            ->get();

        if ($duplikat->isEmpty() && $keNonKolektor->isEmpty()) {
            $this->info('Penugasan kolektor sehat: tidak ada duplikat maupun penugasan ke akun non-kolektor.');

            return self::SUCCESS;
        }

        if ($duplikat->isNotEmpty()) {
            $this->error('Nasabah dengan >1 penugasan aktif: '.$duplikat->count());

            $barisDuplikat = [];
            foreach ($duplikat as $nasabahId) {
                $barisDuplikat[] = ['nasabah_id' => $nasabahId];
            }

            $this->table(['nasabah_id'], $barisDuplikat);
        }

        if ($keNonKolektor->isNotEmpty()) {
            $this->error('Penugasan aktif ke akun non-kolektor: '.$keNonKolektor->count());

            $barisNonKolektor = [];
            foreach ($keNonKolektor as $row) {
                $barisNonKolektor[] = [
                    'id' => $row->id,
                    'kolektor_id' => $row->kolektor_id,
                    'nasabah_id' => $row->nasabah_id,
                    'peran_kolektor' => (string) $row->getAttribute('peran_kolektor'),
                    'nama_kolektor' => (string) $row->getAttribute('nama_kolektor'),
                ];
            }

            $this->table(
                ['id', 'kolektor_id', 'nasabah_id', 'peran_kolektor', 'nama_kolektor'],
                $barisNonKolektor,
            );
        }

        return self::FAILURE;
    }
}
