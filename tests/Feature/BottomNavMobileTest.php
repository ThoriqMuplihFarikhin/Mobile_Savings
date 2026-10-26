<?php

use App\Models\AbsensiKolektor;
use App\Models\User;

/**
 * DESIGN.md §7 "Navigasi mobile": bar putih 64px, empat item dengan label
 * 12px yang selalu terlihat, tombol Absen 56px datar di tengah.
 */
function potongBottomNav(string $html): string
{
    $mulai = strpos($html, 'data-test="bottom-nav"');

    if ($mulai === false) {
        return '';
    }

    return (string) substr($html, $mulai, strpos($html, '</nav>', $mulai) - $mulai);
}

it('menampilkan keempat label bottom nav nasabah meski bukan item aktif', function () {
    $nasabah = User::factory()->nasabah()->create();

    $html = $this->actingAs($nasabah)->get(route('dashboard'))->assertOk()->getContent();

    expect(potongBottomNav($html))
        ->toContain('Beranda')
        ->toContain('Riwayat')
        ->toContain('Paket')
        ->toContain('Profil')
        ->toContain('text-xs')
        ->not->toContain('uppercase');
});

it('menampilkan tombol Absen datar berlabel Absen sebelum kolektor absen', function () {
    $kolektor = User::factory()->kolektor()->create();

    $html = $this->actingAs($kolektor)->get(route('dashboard'))->assertOk()->getContent();

    expect(potongBottomNav($html))
        ->toContain('data-test="tombol-absen"')
        ->toContain('>Absen</span>')
        ->not->toContain('Sudah absen')
        ->not->toContain('bg-gradient')
        ->not->toContain('backdrop-blur');
});

it('menampilkan label Sudah absen setelah kolektor absen hari ini', function () {
    $kolektor = User::factory()->kolektor()->create();

    AbsensiKolektor::create([
        'kolektor_id' => $kolektor->id,
        'tanggal' => now()->toDateString(),
        'waktu_masuk' => now()->toTimeString(),
        'latitude' => -6.2,
        'longitude' => 106.8,
    ]);

    $html = $this->actingAs($kolektor)->get(route('dashboard'))->assertOk()->getContent();

    expect(potongBottomNav($html))
        ->toContain('>Sudah absen</span>')
        ->not->toContain('>Absen</span>');
});
