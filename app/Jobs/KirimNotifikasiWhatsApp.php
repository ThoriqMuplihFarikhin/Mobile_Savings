<?php

namespace App\Jobs;

use App\Models\LogNotifikasi;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class KirimNotifikasiWhatsApp implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah percobaan kirim sebelum job dianggap gagal permanen.
     */
    public int $tries = 3;

    /**
     * Jeda antar percobaan (detik): 30, 120, 600.
     *
     * @var list<int>
     */
    public array $backoff = [30, 120, 600];

    public function __construct(
        public int $logId,
        public string $noHp,
        public string $pesan,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = LogNotifikasi::find($this->logId);

        if (! $log) {
            return;
        }

        if (app(WhatsAppService::class)->sendNotification($this->noHp, $this->pesan)) {
            $log->update(['status_kirim' => 'terkirim', 'waktu_kirim' => now()]);

            return;
        }

        throw new \RuntimeException('Kirim WhatsApp gagal, akan dicoba ulang oleh queue.');
    }

    /**
     * Dipanggil framework setelah seluruh percobaan ($tries) habis — status log
     * ditandai permanen `gagal`.
     */
    public function failed(?\Throwable $e): void
    {
        LogNotifikasi::find($this->logId)?->update([
            'status_kirim' => 'gagal',
            'waktu_kirim' => now(),
        ]);
    }
}
