<?php

use App\Helpers\ActivityLogger;
use App\Models\LogNotifikasi;
use App\Models\User;
use App\Support\JenisNotifikasi;

/**
 * Fase E butir F: registry jenis_notifikasi. Sebelumnya string lepas ('in_app',
 * 'whatsapp', 'both', 'pin_awal') di-hardcode di pemanggil notify(); kini nilai
 * terpusat di App\Support\JenisNotifikasi dan nilai enum = nilai tersimpan di
 * kolom log_notifikasi.jenis_notifikasi.
 */
it('mendaftarkan seluruh nilai jenis_notifikasi yang tersimpan di basis data', function () {
    expect(array_map(static fn (JenisNotifikasi $jenis) => $jenis->value, JenisNotifikasi::cases()))
        ->toBe(['in_app', 'whatsapp', 'both', 'pin_awal']);
});

it('mengklasifikasikan tujuan kirim tiap jenis notifikasi', function () {
    expect([
        JenisNotifikasi::InApp->keAplikasi(),
        JenisNotifikasi::InApp->keWhatsapp(),
        JenisNotifikasi::Whatsapp->keAplikasi(),
        JenisNotifikasi::Whatsapp->keWhatsapp(),
        JenisNotifikasi::Both->keAplikasi(),
        JenisNotifikasi::Both->keWhatsapp(),
        JenisNotifikasi::PinAwal->keAplikasi(),
        JenisNotifikasi::PinAwal->keWhatsapp(),
    ])->toBe([true, false, false, true, true, true, false, false]);
});

it('notify menulis nilai enum ke kolom jenis_notifikasi', function () {
    $nasabah = User::factory()->nasabah()->create(['no_hp' => null]);

    ActivityLogger::notify($nasabah->id, 'Judul uji', 'Pesan uji registry.', JenisNotifikasi::Both);

    $baris = LogNotifikasi::where('nasabah_id', $nasabah->id)->get();

    expect($baris->pluck('channel')->all())->toBe(['in_app', 'whatsapp'])
        ->and($baris->pluck('jenis_notifikasi')->unique()->values()->all())->toBe(['both']);
});
