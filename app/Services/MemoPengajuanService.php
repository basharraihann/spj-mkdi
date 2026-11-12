<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MemoPengajuanService
{
    // Dipanggil unit kerja. Belum mengembalikan apa pun, hanya mencatat pengajuan.
    public static function ajukan(string $memo, string $alasan, int $userId): int
    {
        if (blank($alasan)) {
            throw new \DomainException('Alasan pengajuan wajib diisi.');
        }

        return DB::transaction(function () use ($memo, $alasan, $userId) {
            // Urutan lock: progres dulu, baru pengajuan.
            $tahap = MemoTahapService::tahapTerkunci($memo);

            if (!MemoTahapService::bolehDikembalikan($tahap)) {
                throw new \DomainException("Memo di tahap {$tahap} tidak bisa diajukan pengembalian.");
            }

            $ada = DB::table('memo_pengajuan_kembali')
                ->where('memo_key', $memo)->where('status', 'menunggu')->exists();
            if ($ada) {
                throw new \DomainException('Masih ada pengajuan yang menunggu keputusan.');
            }

            return DB::table('memo_pengajuan_kembali')->insertGetId([
                'memo_key' => $memo,
                'diajukan_oleh' => $userId,
                'alasan' => $alasan,
                'status' => 'menunggu',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    // Dipanggil Keuangan (can:kelola-anggaran).
    public static function putuskan(int $pengajuanId, bool $setuju, ?string $catatan, int $userId): void
    {
        if (!$setuju && blank($catatan)) {
            throw new \DomainException('Catatan wajib diisi jika pengajuan ditolak.');
        }

        $memo = DB::table('memo_pengajuan_kembali')->where('id', $pengajuanId)->value('memo_key');
        if (!$memo) {
            throw new \DomainException('Pengajuan tidak ditemukan.');
        }

        DB::transaction(function () use ($pengajuanId, $memo, $setuju, $catatan, $userId) {
            // Urutan lock sama dengan ajukan(): progres dulu, baru pengajuan.
            MemoTahapService::tahapTerkunci($memo);

            $p = DB::table('memo_pengajuan_kembali')->where('id', $pengajuanId)->lockForUpdate()->first();
            if (!$p) {
                throw new \DomainException('Pengajuan tidak ditemukan.');
            }
            if ($p->status !== 'menunggu') {
                throw new \DomainException('Pengajuan sudah diputuskan.');
            }

            // Putusan ditulis dulu, supaya kembalikan() tidak menggugurkannya sebagai "menunggu".
            DB::table('memo_pengajuan_kembali')->where('id', $pengajuanId)->update([
                'status' => $setuju ? 'disetujui' : 'ditolak',
                'diputus_oleh' => $userId,
                'diputus_at' => now(),
                'catatan_putusan' => $catatan,
                'updated_at' => now(),
            ]);

            if ($setuju) {
                // Melempar DomainException (dan membatalkan semuanya) kalau memo sudah ppspm.
                MemoTahapService::kembalikan($memo, "Pengajuan unit kerja: {$p->alasan}", $userId);
            }
        });
    }
}