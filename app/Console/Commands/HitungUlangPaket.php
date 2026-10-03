<?php

namespace App\Console\Commands;

use App\Models\KepesertaanPaket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('paket:hitung-ulang')]
#[Description('Hitung ulang tunggakan dan status alert kepesertaan paket yang masih berjalan')]
class HitungUlangPaket extends Command
{
    public function handle(): int
    {
        $jumlah = 0;

        KepesertaanPaket::whereNull('keputusan_akhir')
            ->chunkById(200, function ($kepesertaanList) use (&$jumlah) {
                foreach ($kepesertaanList as $kepesertaan) {
                    $kepesertaan->hitungUlangKepesertaan();
                    $jumlah++;
                }
            });

        $this->info("Hitung ulang selesai: {$jumlah} kepesertaan.");

        return self::SUCCESS;
    }
}
