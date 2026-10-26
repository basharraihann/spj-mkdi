<?php

namespace App\Support;

/**
 * Penulis .xlsx sederhana tanpa dependensi (tanpa ZipArchive / paket Composer).
 * Mendukung beberapa sheet, baris judul tebal, angka dan teks.
 *
 * Pemakaian:
 *   $bytes = SimpleXlsx::build([
 *       'Nama Sheet' => ['headers' => ['A', 'B'], 'rows' => [['x', 1], ['y', 2]]],
 *   ]);
 */
class SimpleXlsx
{
    /** @param array<string, array{headers: array, rows: array}> $sheets */
    public static function build(array $sheets): string
    {
        $names = [];
        $files = [];
        $i = 0;

        foreach ($sheets as $nama => $data) {
            $i++;
            $names[$i] = self::namaSheet($nama, $names);
            $files["xl/worksheets/sheet{$i}.xml"] = self::sheetXml($data['headers'] ?? [], $data['rows'] ?? []);
        }

        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        foreach ($names as $n => $_) {
            $ct .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $ct .= '</Types>';

        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($names as $n => $nama) {
            $wb .= '<sheet name="' . self::esc($nama) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $rels .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        }
        $wb .= '</sheets></workbook>';
        $rels .= '<Relationship Id="rIdStyles" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        $files['[Content_Types].xml'] = $ct;
        $files['_rels/.rels'] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
        $files['xl/workbook.xml'] = $wb;
        $files['xl/_rels/workbook.xml.rels'] = $rels;
        $files['xl/styles.xml'] = $styles;

        return self::zip($files);
    }

    private static function sheetXml(array $headers, array $rows): string
    {
        $lebar = [];
        $semua = array_merge([$headers], $rows);
        foreach ($semua as $r) {
            foreach (array_values($r) as $c => $v) {
                $lebar[$c] = max($lebar[$c] ?? 8, min(60, mb_strlen((string) $v) + 2));
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
        if ($lebar) {
            $xml .= '<cols>';
            foreach ($lebar as $c => $w) {
                $xml .= '<col min="' . ($c + 1) . '" max="' . ($c + 1) . '" width="' . $w . '" customWidth="1"/>';
            }
            $xml .= '</cols>';
        }
        $xml .= '<sheetData>';

        $n = 0;
        if ($headers) {
            $n++;
            $xml .= self::rowXml($n, $headers, true);
        }
        foreach ($rows as $r) {
            $n++;
            $xml .= self::rowXml($n, $r, false);
        }

        return $xml . '</sheetData></worksheet>';
    }

    private static function rowXml(int $n, array $cells, bool $header): string
    {
        $xml = '<row r="' . $n . '">';
        foreach (array_values($cells) as $c => $v) {
            $ref = self::kolom($c) . $n;
            $s = $header ? ' s="1"' : '';
            if (is_int($v) || is_float($v)) {
                $xml .= '<c r="' . $ref . '"' . $s . '><v>' . $v . '</v></c>';
            } else {
                $xml .= '<c r="' . $ref . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">' . self::esc((string) $v) . '</t></is></c>';
            }
        }

        return $xml . '</row>';
    }

    private static function kolom(int $i): string
    {
        $s = '';
        $i++;
        while ($i > 0) {
            $m = ($i - 1) % 26;
            $s = chr(65 + $m) . $s;
            $i = intdiv($i - 1, 26);
        }

        return $s;
    }

    private static function esc(string $v): string
    {
        $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $v) ?? '';

        return htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private static function namaSheet(string $nama, array $dipakai): string
    {
        $nama = preg_replace('/[\\[\\]:*?\\/\\\\]/', ' ', $nama) ?? 'Sheet';
        $nama = mb_substr(trim($nama) ?: 'Sheet', 0, 31);
        $asli = $nama;
        $k = 2;
        while (in_array($nama, $dipakai, true)) {
            $nama = mb_substr($asli, 0, 28) . ' ' . $k++;
        }

        return $nama;
    }

    /** ZIP tanpa kompresi (method 0) murni PHP. */
    private static function zip(array $files): string
    {
        $data = '';
        $central = '';
        $offset = 0;
        $dos = self::dosTime();

        foreach ($files as $nama => $isi) {
            $crc = crc32($isi);
            $len = strlen($isi);
            $namaLen = strlen($nama);

            $local = pack('VvvvvvVVVvv', 0x04034b50, 20, 0x0800, 0, $dos['time'], $dos['date'], $crc, $len, $len, $namaLen, 0) . $nama;
            $data .= $local . $isi;

            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0x0800, 0, $dos['time'], $dos['date'], $crc, $len, $len, $namaLen, 0, 0, 0, 0, 0, $offset) . $nama;
            $offset += strlen($local) + $len;
        }

        $jumlah = count($files);
        $end = pack('VvvvvVVv', 0x06054b50, 0, 0, $jumlah, $jumlah, strlen($central), strlen($data), 0);

        return $data . $central . $end;
    }

    private static function dosTime(): array
    {
        $t = getdate();

        return [
            'time' => ($t['hours'] << 11) | ($t['minutes'] << 5) | intdiv($t['seconds'], 2),
            'date' => (max(1980, $t['year']) - 1980) << 9 | ($t['mon'] << 5) | $t['mday'],
        ];
    }
}
