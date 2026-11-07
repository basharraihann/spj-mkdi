<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\MemoEntry;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya tempat status anggaran memo diubah:
 * outstanding -> sakti -> sp2d (+ batal), lengkap dengan validasi urutan dan log.
 *
 * Id memo sama dengan di daftar memo:
 *   entry-12            memo mandiri (memo_entries)
 *   agenda-3-pns        memo PNS milik agenda 3
 *   agenda-3-non_pns    memo Non PNS milik agenda 3
 */
class MemoStatusService
{
    public const STATUS = ['outstanding', 'sakti', 'sp2d', 'batal'];

    /** Perpindahan yang diizinkan dari status saat ini. */
    public const BOLEH = [
        'outstanding' => ['sakti', 'batal'],
        'sakti'       => ['sp2d', 'outstanding', 'batal'],
        'sp2d'        => ['sakti', 'batal'],
        'batal'       => ['outstanding'],
    ];

    /** Perpindahan "maju" yang normal. Selain ini (mundur / batal) wajib isi alasan. */
    public const MAJU = ['outstanding' => 'sakti', 'sakti' => 'sp2d'];

    /**
     * Ubah status satu memo.
     *
     * @return bool true kalau status berubah, false kalau sudah berstatus itu.
     * @throws \DomainException kalau perpindahan tidak diizinkan / alasan kurang / id tidak valid.
     */
    public static function ubah(
        string $memo,
        string $ke,
        ?string $tanggal = null,
        ?string $alasan = null,
        ?int $userId = null
    ): bool {
        if (!in_array($ke, self::STATUS, true)) {
            throw new \DomainException('Status tidak dikenal.');
        }

        [$tipe, $id, $jenis] = self::parse($memo);
        $tanggal = $tanggal ?: now()->toDateString();

        $kolom = $tipe === 'entry'
            ? ['status' => 'status_anggaran', 'sakti' => 'tgl_sakti', 'sp2d' => 'tgl_sp2d']
            : ['status' => "status_{$jenis}", 'sakti' => "tgl_sakti_{$jenis}", 'sp2d' => "tgl_sp2d_{$jenis}"];

        return DB::transaction(function () use ($tipe, $id, $jenis, $ke, $tanggal, $alasan, $userId, $kolom) {
            $class = $tipe === 'entry' ? MemoEntry::class : Agenda::class;
            $row = $class::query()->lockForUpdate()->findOrFail($id);

            $dari = $row->{$kolom['status']} ?: 'outstanding';

            if ($dari === $ke) {
                return false;
            }

            if (!in_array($ke, self::BOLEH[$dari] ?? [], true)) {
                throw new \DomainException("Status tidak bisa langsung diubah dari {$dari} ke {$ke}.");
            }

            $maju = (self::MAJU[$dari] ?? null) === $ke;
            if (!$maju && blank($alasan)) {
                throw new \DomainException('Alasan wajib diisi untuk membatalkan atau mundur status.');
            }

            $row->{$kolom['status']} = $ke;

            if ($ke === 'sakti') {
                $row->{$kolom['sakti']} = $row->{$kolom['sakti']} ?? $tanggal;
                $row->{$kolom['sp2d']} = null;
            } elseif ($ke === 'sp2d') {
                $row->{$kolom['sakti']} = $row->{$kolom['sakti']} ?? $tanggal;
                $row->{$kolom['sp2d']} = $tanggal;
            } elseif ($ke === 'outstanding') {
                $row->{$kolom['sakti']} = null;
                $row->{$kolom['sp2d']} = null;
            }

            $row->save();

            DB::table('memo_status_logs')->insert([
                'memo_type'  => $tipe,
                'memo_id'    => $id,
                'jenis'      => $jenis,
                'dari'       => $dari,
                'ke'         => $ke,
                'alasan'     => $alasan,
                'user_id'    => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        });
    }

    /** @return array{0:string,1:int,2:?string} [tipe, id, jenis] */
    public static function parse(string $memo): array
    {
        if (preg_match('/^entry-(\d+)$/', $memo, $m)) {
            return ['entry', (int) $m[1], null];
        }

        if (preg_match('/^agenda-(\d+)-(pns|non_pns)$/', $memo, $m)) {
            return ['agenda', (int) $m[1], $m[2]];
        }

        throw new \DomainException('Id memo tidak valid.');
    }
}
