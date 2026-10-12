<?php

namespace App\Services;

use App\Models\AnggaranItem;
use App\Models\AnggaranUpload;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

/**
 * Membaca "Laporan Ketersediaan Dana Detail" (SAKTI) lalu menyimpannya ke database.
 *
 * Level hierarki tidak ada kolomnya sendiri; ditentukan dari KOLOM TEMPAT TEKS MULAI:
 *   B(1)=Program/Kegiatan  C(2)=KRO/RO  E(4)=Komponen  F(5)=SubKomponen  H(7)=Akun  N(13)=Item
 * Angka: Q(16)=Pagu, W(22)=Real. lalu, X(23)=Real. ini, Y(24)/Z(25)=Real. s.d. periode ini.
 */
class AnggaranImportService
{
    /** Berapa riwayat upload yang disimpan (yang lebih lama dihapus otomatis). */
    private const KEEP_UPLOADS = 5;

    public function import(string $path, string $originalName, ?int $userId): AnggaranUpload
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $rows = $reader->load($path)->getActiveSheet()->toArray(null, false, false, false);

        if (stripos((string) ($rows[0][0] ?? ''), 'KETERSEDIAAN DANA') === false) {
            throw new RuntimeException('Format file tidak dikenali. Gunakan Laporan Ketersediaan Dana Detail dari SAKTI.');
        }

        $items = $this->parseItems($rows);
        if (!$items) {
            throw new RuntimeException('Tidak ada item anggaran yang ditemukan di file ini.');
        }

        return DB::transaction(function () use ($rows, $items, $originalName, $userId) {
            $upload = AnggaranUpload::create([
                'periode' => trim((string) ($rows[2][0] ?? '')),
                'satker_kode' => trim((string) ($rows[5][14] ?? '')),
                'satker_nama' => trim((string) ($rows[5][15] ?? '')),
                'nama_file' => $originalName,
                'jumlah_item' => count($items),
                'total_pagu' => array_sum(array_column($items, 'pagu')),
                'total_realisasi' => array_sum(array_column($items, 'realisasi')),
                'is_active' => false,
                'uploaded_by' => $userId,
            ]);

            foreach (array_chunk($items, 500) as $chunk) {
                AnggaranItem::insert(array_map(
                    fn($i) => $i + ['anggaran_upload_id' => $upload->id],
                    $chunk
                ));
            }

            // Baru aktif setelah semua item masuk. Kalau gagal di atas, transaksi dibatalkan
            // dan data aktif yang lama tetap utuh.
            AnggaranUpload::where('id', '!=', $upload->id)->update(['is_active' => false]);
            $upload->update(['is_active' => true]);

            // Ambil id terbaru dulu, sisanya (di luar KEEP_UPLOADS) dihapus.
            // Jangan pakai skip() tanpa take(): MySQL menolak OFFSET tanpa LIMIT.
            $old = AnggaranUpload::orderByDesc('id')->pluck('id')->slice(self::KEEP_UPLOADS);
            if ($old->isNotEmpty()) {
                AnggaranUpload::whereIn('id', $old)->delete();
            }

            return $upload->fresh();
        });
    }

    private function parseItems(array $rows): array
    {
        $order = ['prog', 'keg', 'kro', 'ro', 'komp', 'sub', 'akun'];
        $ctx = [];
        $items = [];

        // Data mulai baris ke-10 (index 9); baris 9 adalah "JUMLAH SELURUHNYA".
        for ($i = 9, $n = count($rows); $i < $n; $i++) {
            $r = $rows[$i];

            $f = null;
            foreach ($r as $idx => $v) {
                if ($v !== null && trim((string) $v) !== '') {
                    $f = $idx;
                    break;
                }
            }
            if ($f === null)
                continue;

            $code = trim((string) $r[$f]);
            if ($code !== '' && $code[0] === '*')
                continue; // catatan "*Lock Pagu..." tiap halaman

            $lvl = match (true) {
                $f === 1 => str_contains($code, '.') ? 'keg' : 'prog',
                $f === 2 => str_contains($code, '.') ? 'ro' : 'kro',
                $f === 4 => 'komp',
                $f === 5 => 'sub',
                $f === 7 => 'akun',
                $f === 13 => 'item',
                default => null,
            };
            if ($lvl === null)
                continue;

            if ($lvl === 'item') {
                if (!preg_match('/^(\d+)\.\s*(.*)$/s', $code, $m)) {
                    // Baris lanjutan: SAKTI memotong uraian panjang di pergantian halaman dan
                    // meneruskannya di baris berikutnya dengan angka yang SAMA. Gabungkan ke
                    // uraian item sebelumnya; jangan dihitung sebagai item baru (nanti dobel).
                    if ($items) {
                        $last = array_key_last($items);
                        $items[$last]['item_uraian'] = rtrim($items[$last]['item_uraian']) . ' ' . $code;
                    }
                    continue;
                }
                [$code, $name] = [$m[1], trim($m[2])];

                $pagu = is_numeric($r[16] ?? null) ? $r[16] : 0;
                if (is_numeric($r[24] ?? null))
                    $real = $r[24];
                elseif (is_numeric($r[25] ?? null))
                    $real = $r[25];
                else
                    $real = (is_numeric($r[22] ?? null) ? $r[22] : 0) + (is_numeric($r[23] ?? null) ? $r[23] : 0);

                $p = fn($k) => $ctx[$k] ?? ['c' => '', 'n' => ''];
                $items[] = [
                    'keg_kode' => $p('keg')['c'],
                    'keg_uraian' => $p('keg')['n'],
                    'ro_kode' => $p('ro')['c'],
                    'ro_uraian' => $p('ro')['n'],
                    'komp_kode' => $p('komp')['c'],
                    'komp_uraian' => $p('komp')['n'],
                    'sub_kode' => $p('sub')['c'],
                    'sub_uraian' => $p('sub')['n'],
                    'akun_kode' => $p('akun')['c'],
                    'akun_uraian' => $p('akun')['n'],
                    'item_kode' => $code,
                    'item_uraian' => $name,
                    'pagu' => (int) round($pagu),
                    'realisasi' => (int) round($real),
                ];
                continue;
            }

            $name = '';
            for ($j = $f + 1; $j < 16; $j++) {
                if (($r[$j] ?? null) !== null && trim((string) $r[$j]) !== '') {
                    $name = trim((string) $r[$j]);
                    break;
                }
            }

            $ctx[$lvl] = ['c' => $code, 'n' => $name];
            for ($k = array_search($lvl, $order) + 1; $k < count($order); $k++) {
                unset($ctx[$order[$k]]);
            }
        }

        return $items;
    }
}