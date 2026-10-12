<?php

namespace App\Support;

class FaLevels
{
    public const ORDER = [
        'program', 'kegiatan', 'output', 'suboutput',
        'komponen', 'subkomponen', 'akun', 'item',
    ];

    public const MAP = [
        'program'     => ['label' => 'Program',     'kodeCol' => 'B', 'textCol' => 'D', 'merges' => ['B:C', 'D:P'], 'sample' => 10, 'color' => '#000000'],
        'kegiatan'    => ['label' => 'Kegiatan',    'kodeCol' => 'B', 'textCol' => 'I', 'merges' => ['B:H', 'I:P'], 'sample' => 11, 'color' => '#000000'],
        'output'      => ['label' => 'Output',      'kodeCol' => 'C', 'textCol' => 'G', 'merges' => ['C:F', 'G:P'], 'sample' => 12, 'color' => '#FF0000'],
        'suboutput'   => ['label' => 'Sub Output',  'kodeCol' => 'C', 'textCol' => 'K', 'merges' => ['C:J', 'K:P'], 'sample' => 13, 'color' => '#CC00CC'],
        'komponen'    => ['label' => 'Komponen',    'kodeCol' => 'E', 'textCol' => 'J', 'merges' => ['E:I', 'J:P'], 'sample' => 14, 'color' => '#CC9900'],
        'subkomponen' => ['label' => 'Sub Komponen', 'kodeCol' => 'F', 'textCol' => 'L', 'merges' => ['F:K', 'L:P'], 'sample' => 15, 'color' => '#000000'],
        'akun'        => ['label' => 'Akun',        'kodeCol' => 'H', 'textCol' => 'M', 'merges' => ['H:L', 'M:P'], 'sample' => 16, 'color' => '#000000'],
        'item'        => ['label' => 'Item',        'kodeCol' => 'N', 'textCol' => null, 'merges' => ['N:P'],        'sample' => 17, 'color' => '#000000'],
    ];

    /** Merge kolom angka yang sama untuk semua level. */
    public const VALUE_MERGES = ['Q:R', 'S:V', 'X:Y', 'Z:AB', 'AC:AD', 'AE:AI'];

    /** Kolom angka pada Excel: field => kolom. */
    public const VALUE_COLS = [
        'pagu'          => 'Q',
        'lock_pagu'     => 'S',
        'realisasi_lalu' => 'W',
        'realisasi_ini' => 'X',
        'realisasi_sd'  => 'Z',
        'persen'        => 'AC',
        'sisa'          => 'AE',
    ];

    /** Perkiraan tinggi baris Excel dari panjang teks (dikalibrasi dari file asli: ±56 karakter per baris). */
    public static function guessHeight(string $level, string $text): float
    {
        if (in_array($level, ['program', 'output', 'akun'], true)) {
            return 15.0;
        }
        $lines = max(1, (int) ceil(mb_strlen($text) / 56));

        return $lines >= 3 ? 27.0 : ($lines === 2 ? 18.0 : 15.0);
    }

    public static function rank(string $level): int
    {
        $i = array_search($level, self::ORDER, true);

        return $i === false ? -1 : $i;
    }

    public static function child(string $level): ?string
    {
        $i = self::rank($level);

        return self::ORDER[$i + 1] ?? null;
    }

    public static function labels(): array
    {
        return array_map(fn ($m) => $m['label'], self::MAP);
    }
}