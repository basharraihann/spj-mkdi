<?php

namespace App\Support;

/**
 * Jembatan data "anggaran" (baris datar per item: keg_kode, ro_kode, komp_kode, sub_kode, akun_kode, item_kode, pagu, realisasi)
 * -> file Excel mengikuti template Laporan Ketersediaan Dana Detail.
 *
 * Kolom opsional (dipakai kalau ada di data): prog_kode/prog_uraian, lock_pagu, realisasi_lalu, realisasi_ini.
 */
class AnggaranExcel
{
    public const TEMPLATE = 'app/templates/fa_template.xlsx';

    public const CATATAN = '*Lock Pagu adalah jumlah pagu yang sedang dalam proses usulan revisi DIPA atau POK. Lock pagu akan hilang setelah usulan revisi DIPA/POK selesai menjadi DIPA.';

    /** kode filter => [level template, field kode, field uraian] */
    private const CHAIN = [
        'prog' => ['program', 'prog_kode', 'prog_uraian'],
        'keg'  => ['kegiatan', 'keg_kode', 'keg_uraian'],
        'ro'   => ['suboutput', 'ro_kode', 'ro_uraian'],
        'komp' => ['komponen', 'komp_kode', 'komp_uraian'],
        'sub'  => ['subkomponen', 'sub_kode', 'sub_uraian'],
        'akun' => ['akun', 'akun_kode', 'akun_uraian'],
    ];

    /** Unduh file .xlsx. $filters: keg, ro, komp, sub, akun, q, level (sama dengan filter di layar). */
    public static function download(iterable $items, $upload = null, array $filters = [])
    {
        $list = [];
        foreach ($items as $it) {
            $list[] = $it;
        }
        [$rows, $totals] = self::buildRows($list, $filters);

        $header = ['catatan' => self::CATATAN, 'hal_total' => (string) max(1, (int) ceil(count($rows) / 25))];
        if ($upload) {
            $periode = trim((string) self::g($upload, 'periode'));
            if ($periode !== '') {
                $header['periode'] = stripos($periode, 'periode') === 0 ? $periode : 'Periode ' . $periode;
            }
            if (($s = trim((string) self::g($upload, 'satker_nama'))) !== '') {
                $header['satker_nama'] = $s;
            }
        }

        $exporter = new FaXlsxExporter(storage_path(self::TEMPLATE));
        $book = $exporter->build($rows, $header, $totals);
        $tmp = tempnam(sys_get_temp_dir(), 'ang_') . '.xlsx';
        $exporter->save($book, $tmp);

        return response()->download($tmp, 'Laporan_Anggaran_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** @return array{0: array<int,array>, 1: array} [baris hierarki, total] */
    public static function buildRows(array $items, array $filters = []): array
    {
        $hasProg = false;
        foreach ($items as $it) {
            if (trim((string) self::g($it, 'prog_kode')) !== '') {
                $hasProg = true;
                break;
            }
        }
        $chain = array_filter(self::CHAIN, fn ($k) => $hasProg || $k !== 'prog', ARRAY_FILTER_USE_KEY);

        $items = array_values(array_filter($items, fn ($it) => self::passes($it, $filters)));

        $root = ['children' => [], 'leaf' => []];
        $tot = ['pagu' => 0.0, 'lock_pagu' => 0.0, 'realisasi_lalu' => null, 'realisasi_ini' => null, 'realisasi_sd' => 0.0];

        foreach ($items as $it) {
            $v = self::values($it);
            self::add($tot, $v);
            $node = &$root;
            foreach ($chain as $key => [$level, $fk, $fu]) {
                $kode = trim((string) self::g($it, $fk));
                if (! isset($node['children'][$kode])) {
                    $node['children'][$kode] = [
                        'level' => $level, 'kode' => self::kode($level, $kode), 'uraian' => trim((string) self::g($it, $fu)),
                        'v' => ['pagu' => 0.0, 'lock_pagu' => 0.0, 'realisasi_lalu' => null, 'realisasi_ini' => null, 'realisasi_sd' => 0.0],
                        'children' => [], 'leaf' => [],
                    ];
                }
                self::add($node['children'][$kode]['v'], $v);
                $node = &$node['children'][$kode];
            }
            $node['leaf'][] = [
                'level' => 'item', 'kode' => trim((string) self::g($it, 'item_kode')),
                'uraian' => trim((string) self::g($it, 'item_uraian')), 'v' => $v,
            ];
            unset($node);
        }

        $rows = [];
        self::flatten($root, $rows);

        $only = (string) ($filters['level'] ?? '');
        $map = ['prog' => 'program', 'keg' => 'kegiatan', 'ro' => 'suboutput', 'komp' => 'komponen', 'sub' => 'subkomponen', 'akun' => 'akun', 'item' => 'item'];
        if (isset($map[$only])) {
            $rows = array_values(array_filter($rows, fn ($r) => $r['level'] === $map[$only]));
        }

        return [$rows, $tot];
    }

    /* ------------------------------------------------------------------ */

    private static function flatten(array $node, array &$out): void
    {
        foreach ($node['children'] as $c) {
            $out[] = self::row($c['level'], $c['kode'], $c['uraian'], $c['v']);
            self::flatten($c, $out);
        }
        foreach ($node['leaf'] as $l) {
            $out[] = self::row($l['level'], $l['kode'], $l['uraian'], $l['v']);
        }
    }

    private static function row(string $level, string $kode, string $uraian, array $v): array
    {
        $pagu = $v['pagu'];
        $sd = $v['realisasi_sd'];

        return [
            'level' => $level, 'kode' => $kode, 'uraian' => $uraian,
            'pagu' => $pagu, 'lock_pagu' => $v['lock_pagu'],
            'realisasi_lalu' => $v['realisasi_lalu'], 'realisasi_ini' => $v['realisasi_ini'],
            'realisasi_sd' => $sd,
            'persen' => $pagu > 0 ? $sd / $pagu : 0,
            'sisa' => $pagu - $sd,
            'row_height' => FaLevels::guessHeight($level, ($level === 'item' && $kode !== '' ? $kode . '. ' : '') . $uraian),
        ];
    }

    private static function values($it): array
    {
        $num = fn ($k) => ($x = self::g($it, $k)) === null || $x === '' ? null : (float) $x;
        $lalu = $num('realisasi_lalu');
        $ini = $num('realisasi_ini');
        $sd = $num('realisasi');
        if ($sd === null) {
            $sd = ($lalu ?? 0) + ($ini ?? 0);
        }

        return [
            'pagu' => (float) ($num('pagu') ?? 0), 'lock_pagu' => (float) ($num('lock_pagu') ?? 0),
            'realisasi_lalu' => $lalu, 'realisasi_ini' => $ini, 'realisasi_sd' => $sd,
        ];
    }

    private static function add(array &$to, array $v): void
    {
        foreach (['pagu', 'lock_pagu', 'realisasi_sd'] as $k) {
            $to[$k] += $v[$k];
        }
        foreach (['realisasi_lalu', 'realisasi_ini'] as $k) {
            if ($v[$k] !== null) {
                $to[$k] = ($to[$k] ?? 0) + $v[$k];
            }
        }
    }

    private static function passes($it, array $f): bool
    {
        foreach (['keg', 'ro', 'komp', 'sub', 'akun'] as $k) {
            $want = (string) ($f[$k] ?? '');
            if ($want !== '' && (string) self::g($it, $k . '_kode') !== $want) {
                return false;
            }
        }
        $q = trim((string) ($f['q'] ?? ''));
        if ($q === '') {
            return true;
        }
        $hay = [];
        foreach (self::CHAIN as [$level, $fk, $fu]) {
            $k = (string) self::g($it, $fk);
            $hay[] = mb_strtolower($k);
            $hay[] = mb_strtolower(self::kode($level, $k));
            $hay[] = mb_strtolower((string) self::g($it, $fu));
        }
        $hay[] = mb_strtolower((string) self::g($it, 'item_kode'));
        $hay[] = mb_strtolower((string) self::g($it, 'item_uraian'));
        $hay = implode("\n", $hay);
        foreach (preg_split('/[,;\n]+/', mb_strtolower($q)) as $t) {
            $t = trim($t);
            if ($t !== '' && str_contains($hay, $t)) {
                return true;
            }
        }

        return false;
    }

    /** komponen "51" -> "051" (sama seperti di file Excel asli) */
    private static function kode(string $level, string $kode): string
    {
        return ($level === 'komponen' && $kode !== '' && ctype_digit($kode)) ? str_pad($kode, 3, '0', STR_PAD_LEFT) : $kode;
    }

    private static function g($o, string $k)
    {
        return is_array($o) ? ($o[$k] ?? null) : ($o->{$k} ?? null);
    }
}