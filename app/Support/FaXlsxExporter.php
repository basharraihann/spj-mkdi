<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FaXlsxExporter
{
    private const FIRST_COL = 1;   // A
    private const LAST_COL = 35;   // AI
    private const START_ROW = 10;
    private const FOOT_SAMPLE_ROW = 18;

    public function __construct(private string $templatePath)
    {
    }

    /**
     * @param  iterable<array<string,mixed>>  $rows   urut sesuai sort_order
     * @param  array<string,mixed>            $header
     */
    public function build(iterable $rows, array $header, ?array $totals = null): Spreadsheet
    {
        @ini_set('memory_limit', '1024M');
        @set_time_limit(300);

        $book = IOFactory::load($this->templatePath);
        $ws = $book->getSheet(0);

        $sampleXf = $this->collectSampleStyles($ws);
        $footXf = $this->collectRowStyle($ws, self::FOOT_SAMPLE_ROW);
        $sampleH = [];
        foreach (FaLevels::MAP as $lv => $m) {
            $sampleH[$lv] = $ws->getRowDimension($m['sample'])->getRowHeight();
        }
        $footH = $ws->getRowDimension(self::FOOT_SAMPLE_ROW)->getRowHeight();

        // buang baris contoh (10..18) + merge-nya, lalu tulis ulang dari baris 10
        foreach ($ws->getMergeCells() as $range) {
            [$a] = explode(':', $range);
            if ((int) preg_replace('/\D/', '', $a) >= self::START_ROW) {
                $ws->unmergeCells($range);
            }
        }
        for ($r = self::START_ROW; $r <= self::FOOT_SAMPLE_ROW; $r++) {
            for ($c = self::FIRST_COL; $c <= self::LAST_COL; $c++) {
                $ws->setCellValueExplicit([$c, $r], null, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NULL);
            }
        }

        $this->writeHeader($ws, $header);

        $r = self::START_ROW;
        $sum = ['pagu' => 0.0, 'lock_pagu' => 0.0, 'realisasi_lalu' => 0.0, 'realisasi_ini' => 0.0, 'realisasi_sd' => 0.0];

        foreach ($rows as $row) {
            $level = $row['level'];
            if (! isset(FaLevels::MAP[$level])) {
                continue;
            }
            $m = FaLevels::MAP[$level];

            foreach ($sampleXf[$level] as $col => $xf) {
                $ws->getCell([$col, $r])->setXfIndex($xf);
            }

            if ($level === 'item') {
                $text = ($row['kode'] !== '' ? $row['kode'] . '. ' : '') . $row['uraian'];
                $ws->setCellValueExplicit($m['kodeCol'] . $r, $text, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            } else {
                $ws->setCellValueExplicit($m['kodeCol'] . $r, (string) $row['kode'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $ws->setCellValueExplicit($m['textCol'] . $r, (string) $row['uraian'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            foreach (FaLevels::VALUE_COLS as $field => $col) {
                $v = $row[$field] ?? null;
                if ($v !== null && $v !== '') {
                    $ws->setCellValueExplicit($col . $r, (float) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                }
            }

            foreach (array_merge($m['merges'], FaLevels::VALUE_MERGES) as $span) {
                [$a, $b] = explode(':', $span);
                $ws->mergeCells("{$a}{$r}:{$b}{$r}");
            }

            $h = $row['row_height'] ?? null;
            $ws->getRowDimension($r)->setRowHeight(($h !== null && $h > 0) ? (float) $h : ($sampleH[$level] > 0 ? $sampleH[$level] : 15.0));

            if ($level === 'program') {
                foreach (array_keys($sum) as $k) {
                    $sum[$k] += (float) ($row[$k] ?? 0);
                }
            }
            $r++;
        }

        // catatan kaki (satu kali di akhir tabel; file asli mengulangnya tiap halaman cetak)
        $note = trim((string) ($header['catatan'] ?? ''));
        if ($note !== '') {
            foreach ($footXf as $col => $xf) {
                $ws->getCell([$col, $r])->setXfIndex($xf);
            }
            $ws->setCellValueExplicit("B{$r}", $note, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $ws->mergeCells("B{$r}:AF{$r}");
            $ws->getRowDimension($r)->setRowHeight($footH > 0 ? $footH : 12.0);
        }

        // baris 9 = JUMLAH SELURUHNYA (jumlah semua Program pada data yang diekspor)
        $t = $totals ?? $sum;
        $put = fn (string $cell, $v) => $ws->setCellValue($cell, $v === null ? null : (float) $v);
        $pagu = (float) ($t['pagu'] ?? 0);
        $sd = $t['realisasi_sd'] ?? null;
        $put('Q9', $pagu);
        $put('S9', $t['lock_pagu'] ?? 0);
        $put('W9', $t['realisasi_lalu'] ?? null);
        $put('X9', $t['realisasi_ini'] ?? null);
        $put('Y9', $sd);
        $put('AC9', $pagu > 0 ? ((float) $sd) / $pagu : 0);
        $put('AD9', $pagu - (float) $sd);

        // samakan pengaturan cetak dengan file asli: A4 landscape, skala 100% (tanpa "muat 1 halaman")
        $ws->getPageSetup()->setFitToPage(false);
        $ws->getPageSetup()->setScale(100);

        $ws->setSelectedCell('A1');

        return $book;
    }

    public function save(Spreadsheet $book, string $path): void
    {
        $writer = IOFactory::createWriter($book, 'Xlsx');
        $writer->setPreCalculateFormulas(false);
        $writer->save($path);
        $book->disconnectWorksheets();
    }

    private function writeHeader(Worksheet $ws, array $h): void
    {
        $map = [
            'A1' => 'judul', 'A2' => 'subjudul', 'A3' => 'periode',
            'O4' => 'kl_kode', 'P4' => 'kl_nama',
            'O5' => 'unit_kode', 'P5' => 'unit_nama',
            'O6' => 'satker_kode', 'P6' => 'satker_nama',
        ];
        foreach ($map as $cell => $key) {
            if (isset($h[$key]) && $h[$key] !== '') {
                $ws->setCellValueExplicit($cell, (string) $h[$key], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }
        if (isset($h['hal_total']) && $h['hal_total'] !== '') {
            $ws->setCellValueExplicit('AF6', ' ' . trim((string) $h['hal_total']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        }
    }

    /** @return array<string,array<int,int>> level => [kolom => xfIndex] */
    private function collectSampleStyles(Worksheet $ws): array
    {
        $out = [];
        foreach (FaLevels::MAP as $lv => $m) {
            $out[$lv] = $this->collectRowStyle($ws, $m['sample']);
        }

        return $out;
    }

    private function collectRowStyle(Worksheet $ws, int $row): array
    {
        $xf = [];
        for ($c = self::FIRST_COL; $c <= self::LAST_COL; $c++) {
            $xf[$c] = $ws->getCell([$c, $row])->getXfIndex();
        }

        return $xf;
    }
}