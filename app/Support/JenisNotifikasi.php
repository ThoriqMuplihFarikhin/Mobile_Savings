<?php

namespace App\Support;

/**
 * Registry nilai kolom log_notifikasi.jenis_notifikasi.
 *
 * Nilai enum sama dengan nilai tersimpan di basis data; mengubah nilai case
 * berarti mengubah data lama, jadi cukup tambah case baru bila muncul jenis
 * baru. Kanal pengiriman (kolom `channel`) tetap literal terpisah di luar
 * registry ini.
 */
enum JenisNotifikasi: string
{
    /** Notifikasi tampil di dalam aplikasi saja. */
    case InApp = 'in_app';

    /** Notifikasi dikirim lewat WhatsApp saja. */
    case Whatsapp = 'whatsapp';

    /** Notifikasi tampil di aplikasi sekaligus dikirim WhatsApp. */
    case Both = 'both';

    /** Notifikasi PIN awal khusus (WhatsApp, bukan kabar transaksi). */
    case PinAwal = 'pin_awal';

    /**
     * Apakah jenis ini menulis baris notifikasi dalam aplikasi?
     */
    public function keAplikasi(): bool
    {
        return in_array($this, [self::InApp, self::Both], true);
    }

    /**
     * Apakah jenis ini mengantrekan pengiriman WhatsApp?
     */
    public function keWhatsapp(): bool
    {
        return in_array($this, [self::Whatsapp, self::Both], true);
    }
}
