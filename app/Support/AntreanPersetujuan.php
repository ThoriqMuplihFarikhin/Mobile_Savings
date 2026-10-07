<?php

namespace App\Support;

use App\Models\IzinKolektor;
use App\Models\KepesertaanPaket;
use App\Models\Komplain;
use App\Models\NasabahProfil;
use App\Models\SetoranKolektorKantor;
use App\Models\TransaksiPenarikan;

class AntreanPersetujuan
{
    /**
     * Hitungan tiap antrean persetujuan admin: satu query agregat per antrean,
     * tanpa cache agar halaman hub dan lencana bottom-nav selalu real-time.
     *
     * @return array{penarikan: int, verifikasi: int, setoran_kantor: int, komplain: int, izin: int, bermasalah: int}
     */
    public static function ringkas(): array
    {
        return [
            'penarikan' => TransaksiPenarikan::where('status', 'pending')->count(),
            'verifikasi' => NasabahProfil::where('status_pendaftaran', 'pending_verifikasi')->count(),
            'setoran_kantor' => SetoranKolektorKantor::where('status', 'pending')->count(),
            'komplain' => Komplain::where('status', 'baru')->count(),
            'izin' => IzinKolektor::where('status', 'pending')->count(),
            'bermasalah' => KepesertaanPaket::where('status_alert', 'perlu_review')->count(),
        ];
    }

    /**
     * Total antrean untuk lencana jumlah pada navigasi.
     */
    public static function total(): int
    {
        return array_sum(static::ringkas());
    }
}
