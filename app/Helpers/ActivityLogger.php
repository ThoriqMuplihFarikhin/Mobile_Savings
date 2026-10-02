<?php

namespace App\Helpers;

use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    public static function log(string $aksi, string $entitas, int $entitasId, array $detail = [], ?int $userId = null): void
    {
        LogAktivitas::create([
            'user_id' => $userId ?? auth()->id(),
            'aksi' => $aksi,
            'entitas_terkait' => $entitas,
            'entitas_id' => $entitasId,
            'detail' => $detail,
            'ip_address' => Request::ip(),
            'timestamp' => now(),
        ]);
    }

    public static function notify(int $nasabahId, string $judul, string $pesan, string $jenis = 'in_app'): void
    {
        if (in_array($jenis, ['in_app', 'both'])) {
            LogNotifikasi::create([
                'nasabah_id' => $nasabahId,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis_notifikasi' => $jenis,
                'channel' => 'in_app',
                'status_kirim' => 'terkirim',
                'is_read' => false,
                'waktu_kirim' => now(),
            ]);
        }

        if (in_array($jenis, ['whatsapp', 'both'])) {
            $user = User::find($nasabahId);
            $berhasilKirim = false;

            if ($user && $user->no_hp) {
                $whatsapp = app(WhatsAppService::class);
                $berhasilKirim = $whatsapp->sendNotification($user->no_hp, $pesan);
            }

            LogNotifikasi::create([
                'nasabah_id' => $nasabahId,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis_notifikasi' => $jenis,
                'channel' => 'whatsapp',
                'status_kirim' => $berhasilKirim ? 'terkirim' : 'gagal',
                'is_read' => false,
                'waktu_kirim' => now(),
            ]);
        }
    }
}
