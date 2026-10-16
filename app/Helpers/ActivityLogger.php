<?php

namespace App\Helpers;

use App\Jobs\KirimNotifikasiWhatsApp;
use App\Models\LogAktivitas;
use App\Models\LogNotifikasi;
use App\Models\User;
use App\Support\JenisNotifikasi;
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

    public static function notify(int $nasabahId, string $judul, string $pesan, JenisNotifikasi $jenis = JenisNotifikasi::InApp): void
    {
        $user = User::find($nasabahId);

        if ($user !== null && $user->isOffline()) {
            return;
        }

        if ($jenis->keAplikasi()) {
            LogNotifikasi::create([
                'nasabah_id' => $nasabahId,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis_notifikasi' => $jenis->value,
                'channel' => 'in_app',
                'status_kirim' => 'terkirim',
                'is_read' => false,
                'waktu_kirim' => now(),
            ]);
        }

        if (! $jenis->keWhatsapp()) {
            return;
        }

        if (! $user || ! $user->no_hp) {
            LogNotifikasi::create([
                'nasabah_id' => $nasabahId,
                'judul' => $judul,
                'pesan' => $pesan,
                'jenis_notifikasi' => $jenis->value,
                'channel' => 'whatsapp',
                'status_kirim' => 'gagal',
                'is_read' => false,
                'waktu_kirim' => now(),
            ]);

            return;
        }

        if (! $user->notifikasi_wa_aktif) {
            return;
        }

        $log = LogNotifikasi::create([
            'nasabah_id' => $nasabahId,
            'judul' => $judul,
            'pesan' => $pesan,
            'jenis_notifikasi' => $jenis->value,
            'channel' => 'whatsapp',
            'status_kirim' => 'antri',
            'is_read' => false,
            'waktu_kirim' => now(),
        ]);

        try {
            KirimNotifikasiWhatsApp::dispatch($log->id, $user->no_hp, $pesan);
        } catch (\Throwable $e) {
            $log->update(['status_kirim' => 'gagal', 'waktu_kirim' => now()]);
            report($e);
        }
    }
}
