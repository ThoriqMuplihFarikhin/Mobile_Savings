<?php

namespace App\Actions\Pin;

use App\Jobs\KirimPinAwalWhatsApp;
use App\Models\LogNotifikasi;
use App\Models\User;
use App\Services\WhatsAppService;
use App\Support\Pin;
use Illuminate\Support\Facades\Hash;

class KirimPinAwalAction
{
    /**
     * Buat PIN baru (lolos Pin::lemah), paksa ganti PIN, lalu kirim ke nasabah.
     *
     * @return string|null PIN untuk ditampilkan admin sekali lewat flash bila
     *                     WhatsApp tak tersedia; null bila terkirim
     */
    public function buatDanKirim(User $nasabah): ?string
    {
        $pin = Pin::acak();

        $nasabah->update([
            'pin_hash' => Hash::make($pin),
            'harus_ganti_pin' => true,
            'percobaan_gagal' => 0,
            'login_terkunci_hingga' => null,
        ]);

        return $this->kirim($nasabah, $pin) ? null : $pin;
    }

    /**
     * Kirim PIN awal lewat job WhatsApp terenkripsi. log_notifikasi hanya
     * menyimpan pesan tersamar, tanpa PIN.
     *
     * @return bool true bila antre di WhatsApp, false bila fallback ke admin
     */
    public function kirim(User $nasabah, string $pin): bool
    {
        if (! app(WhatsAppService::class)->isConnected() || $nasabah->notifikasi_wa_aktif === false) {
            return false;
        }

        $log = LogNotifikasi::create([
            'nasabah_id' => $nasabah->id,
            'judul' => 'PIN Awal',
            'pesan' => 'PIN awal dikirim ke nomor Anda. Hubungi admin bila tidak menerimanya.',
            'jenis_notifikasi' => 'pin_awal',
            'channel' => 'whatsapp',
            'status_kirim' => 'antri',
            'is_read' => false,
            'waktu_kirim' => now(),
        ]);

        try {
            KirimPinAwalWhatsApp::dispatch($log->id, $nasabah->no_hp, $pin);
        } catch (\Throwable $e) {
            $log->update(['status_kirim' => 'gagal', 'waktu_kirim' => now()]);
            report($e);

            return false;
        }

        return true;
    }
}
