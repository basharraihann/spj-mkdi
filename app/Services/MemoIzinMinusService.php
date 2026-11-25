<?php

namespace App\Services;

use App\Models\MakOption;
use Illuminate\Support\Facades\DB;

/**
 * Izin anggaran minus (nominal memo melebihi sisa efektif MAK).
 *
 * Aturan:
 * 1. Normalnya memo DITOLAK kalau nominal > sisa efektif MAK
 *    (sisa efektif = sisa FA SAKTI - outstanding memo semua unit).
 * 2. Memo boleh tetap disimpan kalau unit kerja mencentang "Ajukan izin minus"
 *    dan mengisi alasan (+ dasar, opsional). Memo tersimpan sebagai draft dengan izin "menunggu".
 * 3. Memo dengan izin "menunggu" atau "ditolak" TIDAK bisa dikirim ke Keuangan.
 * 4. PPK / Keuangan (Gate setujui-minus) menyetujui atau menolak. Setelah disetujui,
 *    memo boleh dikirim walau sisa MAK jadi minus.
 * 5. Kalau memo diedit dan nominal/MAK berubah, izin lama gugur dan harus diajukan ulang.
 *
 * Status izin: menunggu | disetujui | ditolak | gugur (tidak diperlukan lagi / diganti pengajuan baru).
 */
class MemoIzinMinusService
{
    /**
     * Hitung kekurangan anggaran. null = cukup / MAK tidak ada di master (tidak bisa dicek).
     *
     * @return array{mak:string, nominal:int, efektif:int, kurang:int}|null
     */
    public static function hitung(?string $mak, int $nominal, ?int $kecualiEntryId = null): ?array
    {
        $mak = trim((string) $mak);
        if ($mak === '') {
            return null;
        }

        $opt = MakOption::where('mak', $mak)->first();
        if (!$opt) {
            return null;
        }

        $out = MakSisaService::outstanding($kecualiEntryId, null, true)[$mak] ?? 0;
        $efektif = (int) $opt->sisa - (int) $out;

        if ($nominal <= $efektif) {
            return null;
        }

        return ['mak' => $mak, 'nominal' => $nominal, 'efektif' => $efektif, 'kurang' => $nominal - $efektif];
    }

    /** Baris izin terakhir untuk satu memo (null kalau belum pernah). */
    public static function terakhir(string $memoKey): ?object
    {
        return DB::table('memo_izin_minus')->where('memo_key', $memoKey)->orderByDesc('id')->first();
    }

    /**
     * Izin terakhir untuk banyak memo sekaligus (tanpa N+1).
     *
     * @return array<string, object>
     */
    public static function peta(array $memoKeys): array
    {
        if ($memoKeys === []) {
            return [];
        }

        $ids = DB::table('memo_izin_minus')
            ->whereIn('memo_key', $memoKeys)
            ->selectRaw('MAX(id) as id')
            ->groupBy('memo_key')
            ->pluck('id');

        return DB::table('memo_izin_minus as i')
            ->leftJoin('users as u', 'u.id', '=', 'i.diajukan_oleh')
            ->whereIn('i.id', $ids)
            ->select('i.*', 'u.name as pengaju')
            ->get()
            ->keyBy('memo_key')
            ->all();
    }

    /**
     * Catat pengajuan izin baru (status menunggu). Izin lama yang masih menunggu ikut digugurkan.
     */
    public static function ajukan(string $memoKey, array $hitung, string $alasan, ?string $dasar, ?int $userId): int
    {
        if (blank($alasan)) {
            throw new \DomainException('Alasan izin minus wajib diisi.');
        }

        return DB::transaction(function () use ($memoKey, $hitung, $alasan, $dasar, $userId) {
            self::gugurkan($memoKey, 'Diganti pengajuan baru.');

            return DB::table('memo_izin_minus')->insertGetId([
                'memo_key' => $memoKey,
                'mak' => $hitung['mak'],
                'nominal' => $hitung['nominal'],
                'sisa_efektif' => $hitung['efektif'],
                'kekurangan' => $hitung['kurang'],
                'alasan' => $alasan,
                'dasar' => $dasar ?: null,
                'diajukan_oleh' => $userId,
                'status' => 'menunggu',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    /** Ubah alasan/dasar izin yang masih menunggu (memo diedit tanpa mengubah MAK & nominal). */
    public static function perbaruiAlasan(int $id, string $alasan, ?string $dasar): void
    {
        DB::table('memo_izin_minus')->where('id', $id)->where('status', 'menunggu')->update([
            'alasan' => $alasan,
            'dasar' => $dasar ?: null,
            'updated_at' => now(),
        ]);
    }

    /** Izin menunggu digugurkan (dipakai saat memo diedit jadi cukup, atau diajukan ulang). */
    public static function gugurkan(string $memoKey, string $catatan): void
    {
        DB::table('memo_izin_minus')
            ->where('memo_key', $memoKey)
            ->where('status', 'menunggu')
            ->update([
                'status' => 'gugur',
                'catatan_putusan' => $catatan,
                'diputus_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /** Keputusan PPK / Keuangan. */
    public static function putuskan(int $id, bool $setuju, ?string $catatan, int $userId): void
    {
        if (!$setuju && blank($catatan)) {
            throw new \DomainException('Catatan wajib diisi jika izin minus ditolak.');
        }

        DB::transaction(function () use ($id, $setuju, $catatan, $userId) {
            $row = DB::table('memo_izin_minus')->where('id', $id)->lockForUpdate()->first();
            if (!$row) {
                throw new \DomainException('Pengajuan izin tidak ditemukan.');
            }
            if ($row->status !== 'menunggu') {
                throw new \DomainException('Pengajuan izin sudah diputuskan.');
            }

            DB::table('memo_izin_minus')->where('id', $id)->update([
                'status' => $setuju ? 'disetujui' : 'ditolak',
                'diputus_oleh' => $userId,
                'diputus_at' => now(),
                'catatan_putusan' => $catatan,
                'updated_at' => now(),
            ]);
        });
    }

    /**
     * Dipanggil sebelum memo dikirim ke Keuangan. Melempar DomainException kalau belum boleh.
     * - Ada izin terakhir menunggu/ditolak  -> tolak.
     * - Ada izin disetujui untuk nominal & MAK yang sama -> boleh.
     * - Tidak ada izin tapi sekarang melebihi sisa (sisa terpakai memo lain) -> tolak, minta ajukan izin.
     */
    public static function pastikanBolehKirim(string $memoKey, ?string $mak, int $nominal, ?int $kecualiEntryId = null): void
    {
        $izin = self::terakhir($memoKey);
        if ($izin && $izin->status === 'gugur') {
            $izin = null;
        }

        if ($izin && $izin->status === 'menunggu') {
            throw new \DomainException('Memo ini melebihi sisa anggaran dan izin minusnya masih menunggu persetujuan PPK/Keuangan.');
        }

        if ($izin && $izin->status === 'disetujui'
            && trim((string) $izin->mak) === trim((string) $mak)
            && (int) $izin->nominal >= $nominal) {
            return;
        }

        if (self::hitung($mak, $nominal, $kecualiEntryId)) {
            throw new \DomainException(
                $izin && $izin->status === 'ditolak'
                ? 'Izin minus memo ini ditolak. Ubah nominal atau MAK, atau ajukan izin baru lewat Edit.'
                : 'Nominal memo melebihi sisa efektif MAK. Buka Edit, centang "Ajukan izin minus", lalu tunggu persetujuan.'
            );
        }
    }
}
