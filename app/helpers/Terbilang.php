<?php

namespace App\Helpers;

class Terbilang
{
    protected static array $angka = [
        '', 'satu', 'dua', 'tiga', 'empat', 'lima',
        'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh',
        'sebelas',
    ];

    /**
     * Convert a number into Indonesian words (angka -> terbilang).
     * Example: 1500000 -> "satu juta lima ratus ribu"
     */
    public static function make(int|float $angka): string
    {
        $angka = (int) $angka;

        if ($angka < 0) {
            return 'minus ' . self::make(abs($angka));
        }

        if ($angka < 12) {
            return trim(self::$angka[$angka]);
        }

        if ($angka < 20) {
            return trim(self::make($angka - 10) . ' belas');
        }

        if ($angka < 100) {
            return trim(self::make(intdiv($angka, 10)) . ' puluh ' . self::make($angka % 10));
        }

        if ($angka < 200) {
            return trim('seratus ' . self::make($angka - 100));
        }

        if ($angka < 1000) {
            return trim(self::make(intdiv($angka, 100)) . ' ratus ' . self::make($angka % 100));
        }

        if ($angka < 2000) {
            return trim('seribu ' . self::make($angka - 1000));
        }

        if ($angka < 1000000) {
            return trim(self::make(intdiv($angka, 1000)) . ' ribu ' . self::make($angka % 1000));
        }

        if ($angka < 1000000000) {
            return trim(self::make(intdiv($angka, 1000000)) . ' juta ' . self::make($angka % 1000000));
        }

        if ($angka < 1000000000000) {
            return trim(self::make(intdiv($angka, 1000000000)) . ' miliar ' . self::make($angka % 1000000000));
        }

        return trim(self::make(intdiv($angka, 1000000000000)) . ' triliun ' . self::make($angka % 1000000000000));
    }
}
