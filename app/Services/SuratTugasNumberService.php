<?php

namespace App\Services;

use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use Illuminate\Support\Facades\DB;

class SuratTugasNumberService
{
    /**
     * Terbitkan ST: ambil nomor berikutnya (counter + row lock + unique index).
     * Nomor tidak pernah dipakai ulang walau ST dibatalkan.
     */
    public function terbitkan(int $suratTugasId): SuratTugas
    {
        return DB::transaction(function () use ($suratTugasId) {
            $st = SuratTugas::lockForUpdate()->findOrFail($suratTugasId);

            if ($st->status !== 'draf') {
                throw new \DomainException('Hanya ST berstatus Draf yang bisa diterbitkan.');
            }

            $tahun = $st->tanggal->year;

            SuratTugasCounter::firstOrCreate(['tahun' => $tahun], ['last_number' => 0]);
            $counter = SuratTugasCounter::where('tahun', $tahun)->lockForUpdate()->first();

            $counter->last_number++;
            $counter->save();

            $st->update([
                'tahun' => $tahun,
                'nomor_urut' => $counter->last_number,
                'nomor' => $counter->last_number.'/'.$st->kode_klasifikasi.'/'.$tahun,
                'status' => 'terbit',
                'diterbitkan_at' => now(),
            ]);

            return $st;
        });
    }

    /** Atur nomor terakhir (mis. biro sudah sampai 203, berikutnya 204). Hanya boleh naik. */
    public function setCounter(int $tahun, int $lastNumber): void
    {
        DB::transaction(function () use ($tahun, $lastNumber) {
            SuratTugasCounter::firstOrCreate(['tahun' => $tahun], ['last_number' => 0]);
            $c = SuratTugasCounter::where('tahun', $tahun)->lockForUpdate()->first();

            $maxTerpakai = (int) SuratTugas::where('tahun', $tahun)->max('nomor_urut');
            if ($lastNumber < max($c->last_number, $maxTerpakai)) {
                throw new \DomainException('Counter tidak boleh diturunkan (nomor tidak boleh dipakai ulang).');
            }
            $c->update(['last_number' => $lastNumber]);
        });
    }
}
