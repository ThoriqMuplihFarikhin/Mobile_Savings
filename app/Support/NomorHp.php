<?php

namespace App\Support;

class NomorHp
{
    public const PATTERN = '/^08\d{8,12}$/';

    public static function normalize(string $noHp): string
    {
        $digits = preg_replace('/\D+/', '', $noHp) ?? '';

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        return $digits;
    }

    public static function valid(string $noHp): bool
    {
        return preg_match(self::PATTERN, $noHp) === 1;
    }
}
