<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogger;
use App\Models\AdminSetting;
use App\Models\TransaksiPenarikan;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('penarikan:kedaluwarsakan')]
#[Description('Tandai pengajuan penarikan pending yang melewati batas hari menjadi kedaluwarsa')]
class KedaluwarsakanPenarikan extends Command
{
    public function handle(): int
    {
        $hari = AdminSetting::get('penarikan_kedaluwarsa_hari', '7');

        if (! is_numeric($hari) || (int) $hari < 1) {
            $hari = '7';
        }

        $batas = now()->subDays((int) $hari);

        $kandidat = TransaksiPenarikan::where('status', 'pending')
            ->where('created_at', '<', $batas)
            ->pluck('id');

        $kedaluwarsa = 0;

        foreach ($kandidat as $id) {
            $penarikan = DB::transaction(function () use ($id): ?TransaksiPenarikan {
                $baris = TransaksiPenarikan::where('id', $id)->lockForUpdate()->first();

                if ($baris === null || $baris->status !== 'pending') {
                    return null;
                }

                $baris->update(['status' => 'kedaluwarsa']);

                return $baris;
            });

            if ($penarikan === null) {
                continue;
            }

            $kedaluwarsa++;

            try {
                ActivityLogger::notify(
                    $penarikan->nasabah_id,
                    'Pengajuan penarikan kedaluwarsa',
                    'Pengajuan penarikan sebesar Rp '.number_format((float) $penarikan->nominal_diminta, 0, ',', '.')
                        .' melewati batas '.(int) $hari.' hari dan ditandai kedaluwarsa. Ajukan pengajuan baru bila masih diperlukan.',
                    'both'
                );
            } catch (\Exception $e) {
                report($e);
            }
        }

        $this->info("Penarikan kedaluwarsa: {$kedaluwarsa} pengajuan.");

        return self::SUCCESS;
    }
}
