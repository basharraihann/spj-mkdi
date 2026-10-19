<?php

namespace App\Services;

use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use Illuminate\Support\Facades\DB;

class SuratTugasNumberService
{
    /**
     * Pesan nomor saat ST masih draf (status tetap draf, form masih bisa diedit).
     * Nomor diambil dari counter yang sama dengan terbitkan(), tidak pernah dipakai ulang.
     */
    public function ambilNomor(int $id, string $tanggal, string $kode): SuratTugas
    {
        return DB::transaction(function () use ($id, $tanggal, $kode) {
            $st = SuratTugas::lockForUpdate()->findOrFail($id);

            if ($st->status !== 'draf') {
                throw new \DomainException('Nomor hanya bisa diambil untuk ST berstatus Draf.');
            }
            if ($st->nomor_urut) {
                return $st; // sudah punya nomor, jangan ambil lagi
            }

            $st->tanggal = $tanggal;
            $st->kode_klasifikasi = $kode;

            return $this->reserve($st);
        });
    }

    /**
     * Terbitkan ST. Kalau sudah punya nomor (hasil ambilNomor) nomor itu dipakai,
     * kalau belum maka nomor diambil sekarang.
     */
    public function terbitkan(int $suratTugasId): SuratTugas
    {
        return DB::transaction(function () use ($suratTugasId) {
            $st = SuratTugas::lockForUpdate()->findOrFail($suratTugasId);

            if ($st->status !== 'draf') {
                throw new \DomainException('Hanya ST berstatus Draf yang bisa diterbitkan.');
            }

            if (!$st->nomor_urut) {
                $st = $this->reserve($st);
            }

            $st->update([
                'status' => 'terbit',
                'diterbitkan_at' => now(),
            ]);

            return $st;
        });
    }

    /** Lihat nomor berikutnya tanpa mengambilnya (tidak mengubah counter). */
    public function nomorBerikutnya(int $tahun, string $kode): string
    {
        $last = (int) SuratTugasCounter::where('tahun', $tahun)->value('last_number');

        return ($last + 1) . '/' . $kode . '/' . $tahun;
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

    /** Ambil nomor berikutnya dari counter. Wajib dipanggil di dalam transaksi. */
    private function reserve(SuratTugas $st): SuratTugas
    {
        $tahun = $st->tanggal->year;

        SuratTugasCounter::firstOrCreate(['tahun' => $tahun], ['last_number' => 0]);
        $counter = SuratTugasCounter::where('tahun', $tahun)->lockForUpdate()->first();

        $counter->last_number++;
        $counter->save();

        $st->fill([
            'tahun' => $tahun,
            'nomor_urut' => $counter->last_number,
            'nomor' => $counter->last_number . '/' . $st->kode_klasifikasi . '/' . $tahun,
        ])->save();

        return $st;
    }
}