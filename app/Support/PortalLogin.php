<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Portal login terpisah (D19): nasabah, kolektor, dan admin.
 */
class PortalLogin
{
    public const NASABAH = 'nasabah';

    public const KOLEKTOR = 'kolektor';

    public const ADMIN = 'admin';

    /**
     * Cookie pilihan portal — sesi sudah di-invalidate Fortify sebelum
     * LogoutResponse dibaca, jadi portal disimpan di cookie (30 hari).
     */
    public const COOKIE = 'portal_login';

    /**
     * @return list<string>
     */
    public static function semua(): array
    {
        return [self::NASABAH, self::KOLEKTOR, self::ADMIN];
    }

    /**
     * Nilai input form login; nilai di luar daftar dianggap nasabah.
     */
    public static function dariInput(Request $request): string
    {
        $portal = $request->input('portal');

        if (is_string($portal) && in_array($portal, self::semua(), true)) {
            return $portal;
        }

        return self::NASABAH;
    }

    /**
     * Nilai portal dari URI halaman login (/login, /login/kolektor, /login/admin).
     */
    public static function dariUri(Request $request): string
    {
        if ($request->is('login/kolektor')) {
            return self::KOLEKTOR;
        }

        if ($request->is('login/admin')) {
            return self::ADMIN;
        }

        return self::NASABAH;
    }

    public static function routeName(string $portal): string
    {
        return match ($portal) {
            self::KOLEKTOR => 'login.kolektor',
            self::ADMIN => 'login.admin',
            default => 'login',
        };
    }

    /**
     * Label peran untuk judul tab, chip, dan teks portal.
     */
    public static function labelPortal(string $portal): string
    {
        return match ($portal) {
            self::KOLEKTOR => 'Kolektor',
            self::ADMIN => 'Admin',
            default => 'Nasabah',
        };
    }

    /**
     * Warna aksen portal (D19 butir 4): nasabah hijau, kolektor biru, admin gelap.
     */
    public static function aksen(string $portal): string
    {
        return match ($portal) {
            self::KOLEKTOR => '#2563eb',
            self::ADMIN => '#171717',
            default => '#16a34a',
        };
    }
}
