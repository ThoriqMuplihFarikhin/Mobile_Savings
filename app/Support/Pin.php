<?php

namespace App\Support;

class Pin
{
    /**
     * PIN yang dilarang selain pola berulang/berurutan.
     */
    private const DILARANG = ['123123', '123321', '112233'];

    /**
     * Apakah PIN 6 digit termasuk lemah: semua digit sama, berurutan
     * naik/turun, atau masuk daftar terlarang.
     */
    public static function lemah(string $pin): bool
    {
        if (preg_match('/^(\d)\1{5}$/', $pin) === 1) {
            return true;
        }

        $naik = true;
        $turun = true;

        for ($i = 1; $i < 6; $i++) {
            $selisih = ord($pin[$i]) - ord($pin[$i - 1]);

            if ($selisih !== 1) {
                $naik = false;
            }

            if ($selisih !== -1) {
                $turun = false;
            }
        }

        return $naik || $turun || in_array($pin, self::DILARANG, true);
    }
}
