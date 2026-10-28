<?php

namespace App\Services;

use App\Models\SuratMasuk;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Nomor agenda otomatis, format: AG-007/D2/X/2026
 * (urut 3 digit / kode unit / bulan romawi / tahun). Nomor urut reset tiap tahun.
 */
class SuratMasukNumberService
{
    private const PREFIX = 'AG';
    private const KODE = 'D2';

    private const ROMAWI = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
        11 => 'XI',
        12 => 'XII',
    ];

    /** Pratinjau nomor berikutnya (tidak mengubah counter). */
    public function nomorBerikutnya(?Carbon $tanggal = null): string
    {
        $tanggal ??= now();
        $tahun = $tanggal->year;

        $last = (int) DB::table('surat_masuk_counters')->where('tahun', $tahun)->value('last_number');
        $last = max($last, $this->urutTertinggiDiData($tahun));

        return $this->format($last + 1, $tanggal);
    }

    /**
     * Ambil dan kunci nomor berikutnya. Panggil di dalam transaksi yang sama dengan
     * pembuatan surat: kalau penyimpanan gagal, nomor ikut dibatalkan (tidak ada lubang).
     */
    public function ambil(?Carbon $tanggal = null): string
    {
        $tanggal ??= now();
        $tahun = $tanggal->year;

        return DB::transaction(function () use ($tanggal, $tahun) {
            // baris counter tahun ini dibuat sekali, dimulai dari nomor tertinggi yang sudah ada di data lama
            DB::table('surat_masuk_counters')->insertOrIgnore([
                'tahun' => $tahun,
                'last_number' => $this->urutTertinggiDiData($tahun),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = DB::table('surat_masuk_counters')->where('tahun', $tahun)->lockForUpdate()->first();
            $urut = max((int) $counter->last_number, $this->urutTertinggiDiData($tahun));

            // lewati nomor yang sudah dipakai (misalnya pernah diketik manual)
            do {
                $urut++;
                $nomor = $this->format($urut, $tanggal);
            } while (SuratMasuk::where('nomor_agenda', $nomor)->exists());

            DB::table('surat_masuk_counters')->where('tahun', $tahun)->update([
                'last_number' => $urut,
                'updated_at' => now(),
            ]);

            return $nomor;
        });
    }

    private function format(int $urut, Carbon $tanggal): string
    {
        return sprintf('%s-%03d/%s/%s/%d', self::PREFIX, $urut, self::KODE, self::ROMAWI[$tanggal->month], $tanggal->year);
    }

    /** Nomor urut tertinggi pada data yang sudah ada untuk tahun itu (0 jika belum ada). */
    private function urutTertinggiDiData(int $tahun): int
    {
        return (int) SuratMasuk::where('nomor_agenda', 'like', self::PREFIX . '-%/' . self::KODE . '/%/' . $tahun)
            ->pluck('nomor_agenda')
            ->map(fn($n) => preg_match('/^' . self::PREFIX . '-(\d+)\//', $n, $m) ? (int) $m[1] : 0)
            ->max();
    }
}