<?php

namespace App\Console\Commands;

use App\Helpers\ActivityLogger;
use App\Models\KepesertaanPaket;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('paket:kirim-pengingat')]
#[Description('Kirim pengingat tunggakan ke nasabah, maksimal sekali per hari per kepesertaan')]
class KirimPengingatTunggakan extends Command
{
    public function handle(): int
    {
        $dikirim = 0;

        KepesertaanPaket::with(['produk'])
            ->whereNull('keputusan_akhir')
            ->where('tunggakan', '>', 0)
            ->chunkById(200, function ($kepesertaanList) use (&$dikirim) {
                foreach ($kepesertaanList as $kepesertaan) {
                    if (! $kepesertaan->nasabah_id) {
                        continue;
                    }

                    $kunci = sprintf(
                        'pengingat-tunggakan:%d:%d:%s',
                        $kepesertaan->nasabah_id,
                        $kepesertaan->produk_id,
                        now()->toDateString(),
                    );

                    if (! Cache::add($kunci, true, now()->addDay())) {
                        continue;
                    }

                    $produkNama = $kepesertaan->produk->nama ?? 'paket tabungan';
                    ActivityLogger::notify(
                        $kepesertaan->nasabah_id,
                        'Pengingat Tunggakan',
                        "Tunggakan {$kepesertaan->tunggakan} hari pada paket {$produkNama}. Mohon segera menabung kembali agar status kepesertaan tetap baik.",
                        'both',
                    );
                    $dikirim++;
                }
            });

        $this->info("Pengingat terkirim: {$dikirim}.");

        return self::SUCCESS;
    }
}
