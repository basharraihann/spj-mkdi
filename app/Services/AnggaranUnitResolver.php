<?php

namespace App\Services;

use App\Models\AnggaranPemetaan;

/**
 * Mencocokkan item anggaran dengan pemetaan unit. Aturan paling spesifik menang:
 *   1) RO + SubKomponen   2) RO + Komponen   3) Kegiatan + RO   4) RO saja
 * Dicocokkan saat data diambil (bukan saat import), jadi mengubah pemetaan
 * tidak perlu upload ulang, dan pemetaan tetap ada waktu upload file baru.
 *
 * Hasil: ['unit' => unit kerja, 'induk' => kelompok di atasnya (default = unit), 'sub_unit' => null]
 */
class AnggaranUnitResolver
{
    public const BELUM = 'Belum dipetakan';

    private array $sub = [];
    private array $komp = [];
    private array $kegRo = [];
    private array $ro = [];

    public function __construct()
    {
        foreach (AnggaranPemetaan::all() as $m) {
            $val = [
                'unit' => $m->unit,
                'induk' => ($m->induk !== null && $m->induk !== '') ? $m->induk : $m->unit,
                'sub_unit' => $m->sub_unit,
            ];
            $ro = self::n($m->ro_kode);

            if ($m->sub_kode !== '')
                $this->sub[$ro . '|' . self::n($m->sub_kode)] = $val;
            elseif ($m->komp_kode !== '')
                $this->komp[$ro . '|' . self::n($m->komp_kode)] = $val;
            elseif ($m->keg_kode !== '')
                $this->kegRo[self::n($m->keg_kode) . '|' . $ro] = $val;
            else
                $this->ro[$ro] = $val;
        }
    }

    private static function n($v): string
    {
        return strtoupper(trim((string) $v));
    }

    /** @param array|object $item punya keg_kode, ro_kode, komp_kode, sub_kode */
    public function resolve($item): array
    {
        $g = fn($k) => self::n(is_array($item) ? ($item[$k] ?? '') : ($item->$k ?? ''));
        $keg = $g('keg_kode');
        $ro = $g('ro_kode');
        $komp = $g('komp_kode');
        $sub = $g('sub_kode');

        return $this->sub[$ro . '|' . $sub]
            ?? $this->komp[$ro . '|' . $komp]
            ?? $this->kegRo[$keg . '|' . $ro]
            ?? $this->ro[$ro]
            ?? ['unit' => self::BELUM, 'induk' => self::BELUM, 'sub_unit' => null];
    }
}