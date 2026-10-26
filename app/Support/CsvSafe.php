<?php

namespace App\Support;

class CsvSafe
{
    /**
     * Sanitasi sel teks agar formula tidak dieksekusi spreadsheet.
     */
    public static function teks(mixed $nilai): string
    {
        if (is_string($nilai)) {
            return preg_match('/^[=+\-@\t\r]/', $nilai) === 1 ? "'".$nilai : $nilai;
        }

        return (string) $nilai;
    }

    /**
     * Tulis BOM UTF-8 agar Excel membaca karakter dengan benar.
     *
     * @param  resource  $file
     */
    public static function mulai($file): void
    {
        fwrite($file, "\xEF\xBB\xBF");
    }
}
