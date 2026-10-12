<?php

namespace App\Support;

class AnggaranExcel
{
    public const TEMPLATE = 'app/templates/fa_template.xlsx';

    public const CATATAN = '*Lock Pagu adalah jumlah pagu yang sedang dalam proses usulan revisi DIPA atau POK. Lock pagu akan hilang setelah usulan revisi DIPA/POK selesai menjadi DIPA.';

    /** Nama program bawaan (dari laporan SAKTI); tambahkan kode lain di sini bila perlu. */
    private const NAMA_PROGRAM = [
        'CL' => 'Program Koordinasi Pelaksanaan Kebijakan',
        'WA' => 'Program Dukungan Manajemen',
    ];

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

        $exporter = new FaXlsxExporter(self::templatePath());
        $book = $exporter->build($rows, $header, $totals);
        $base = tempnam(sys_get_temp_dir(), 'ang_');
        $tmp = $base . '.xlsx';
        $exporter->save($book, $tmp);
        @unlink($base);

        return response()->download($tmp, 'Laporan_Anggaran_' . date('Ymd_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** Cari fa_template.xlsx di beberapa lokasi umum supaya tidak error kalau foldernya sedikit beda. */
    public static function templatePath(): string
    {
        $name = 'fa_template.xlsx';
        $candidates = [
            storage_path('app/templates/' . $name),
            storage_path('app/private/templates/' . $name),
            storage_path('templates/' . $name),
            storage_path('app/' . $name),
            __DIR__ . '/' . $name,
            dirname(__DIR__, 2) . '/storage/app/templates/' . $name,
            base_path($name),
        ];
        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new \RuntimeException("Template Excel '{$name}' tidak ditemukan. Taruh di: " . storage_path('app/templates/' . $name));
    }

    /** @return array{0: array<int,array>, 1: array} [baris hierarki, total] */
    public static function buildRows(array $items, array $filters = []): array
    {
        $chain = self::CHAIN;

        $items = array_values(array_filter($items, fn ($it) => self::passes($it, $filters)));

        $root = ['children' => [], 'leaf' => []];
        $tot = ['pagu' => 0.0, 'lock_pagu' => 0.0, 'realisasi_lalu' => null, 'realisasi_ini' => null, 'realisasi_sd' => 0.0];

        foreach ($items as $it) {
            $v = self::values($it);
            self::add($tot, $v);
            $node = &$root;
            foreach ($chain as $key => [$level, $fk, $fu]) {
                $kode = self::lvKode($it, $key);
                if (! isset($node['children'][$kode])) {
                    $node['children'][$kode] = [
                        'level' => $level, 'kode' => self::kode($level, $kode), 'uraian' => self::lvUraian($it, $key),
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
        foreach (self::CHAIN as $key => [$level]) {
            $k = self::lvKode($it, $key);
            $hay[] = mb_strtolower($k);
            $hay[] = mb_strtolower(self::kode($level, $k));
            $hay[] = mb_strtolower(self::lvUraian($it, $key));
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

    /** Program: prog_kode bila ada, kalau tidak dari awalan kode kegiatan (CL.7458 -> CL). */
    private static function lvKode($it, string $key): string
    {
        if ($key !== 'prog') {
            return trim((string) self::g($it, self::CHAIN[$key][1]));
        }
        $p = trim((string) self::g($it, 'prog_kode'));
        if ($p !== '') {
            return $p;
        }
        $k = trim((string) self::g($it, 'keg_kode'));

        return str_contains($k, '.') ? explode('.', $k, 2)[0] : 'PROG';
    }

    private static function lvUraian($it, string $key): string
    {
        if ($key !== 'prog') {
            return trim((string) self::g($it, self::CHAIN[$key][2]));
        }
        $u = trim((string) self::g($it, 'prog_uraian'));
        if ($u !== '') {
            return $u;
        }
        $k = self::lvKode($it, 'prog');

        return self::NAMA_PROGRAM[$k] ?? ($k === 'PROG' ? 'Program Utama' : 'Program ' . $k);
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