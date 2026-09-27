<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\MemoEntry;
use Illuminate\Support\Collection;

/**
 * Nomor memo itu satu urutan global — gak dibedain PNS/Non-PNS, gak ada
 * reset per tahun. Nomor "terbesar yang pernah dipakai" dihitung dari
 * gabungan dua sumber:
 *   1. agendas.nomor_memo_pns / nomor_memo_non_pns  (nomor lama, teks bebas,
 *      format "323/LS.D1.PPK/KU.00/07/2026" — angka di depan yang dipakai)
 *   2. memo_entries.nomor_urut                       (nomor yang dibuat lewat
 *      tombol "Ambil Nomor Memo" atau form mandiri)
 */
class NomorMemoService
{
    /**
     * Nomor urut terbesar yang pernah dipakai dari kedua sumber. 363 kalau
     * belum ada nomor memo sama sekali (jadi nomor berikutnya mulai dari 364).
     */
    public static function nomorTerbesar(): int
    {
        $dariAgenda = self::semuaNomorDariAgenda()
            ->map(fn($nomor) => self::ekstrakUrutan($nomor))
            ->filter()
            ->max();

        $dariEntries = MemoEntry::max('nomor_urut');

        return max((int) $dariAgenda, (int) $dariEntries, 363); // nomor awal - 1
    }

    public static function nomorBerikutnya(): int
    {
        return self::nomorTerbesar() + 1;
    }

    /**
     * Data lengkap buat tombol "Ambil Nomor Memo": nomor urut berikutnya,
     * "ekor" format (bagian setelah angka) niru nomor terakhir yang pernah
     * dipakai dengan bulan/tahun disesuaikan ke sekarang, dan nomor jadi.
     *
     * Kalau belum pernah ada nomor memo sama sekali, ekor pakai template
     * tebakan default — silakan disesuaikan manual di form kalau beda.
     */
    public static function dataBerikutnya(): array
    {
        $urutan = self::nomorBerikutnya();
        $ekor = self::templateEkor();

        return [
            'urutan' => $urutan,
            'ekor' => $ekor,
            'nomor' => $urutan . $ekor,
        ];
    }

    /**
     * Ambil angka urut di paling depan nomor memo, mis.
     * "323/LS.D1.PPK/KU.00/07/2026" -> 323. Null kalau gak ketemu angka di depan.
     */
    public static function ekstrakUrutan(?string $nomor): ?int
    {
        if (!$nomor) {
            return null;
        }

        return preg_match('/^\s*(\d+)/', $nomor, $m) ? (int) $m[1] : null;
    }

    private static function semuaNomorDariAgenda(): Collection
    {
        return Agenda::query()
            ->where(function ($q) {
                $q->whereNotNull('nomor_memo_pns')->orWhereNotNull('nomor_memo_non_pns');
            })
            ->get(['nomor_memo_pns', 'nomor_memo_non_pns'])
            ->flatMap(fn($a) => [$a->nomor_memo_pns, $a->nomor_memo_non_pns])
            ->filter();
    }

    /**
     * Susun "ekor" (bagian format setelah angka urut) buat nomor memo
     * berikutnya, ditiru dari nomor terakhir yang pernah dipakai, dengan
     * bulan/tahun di dalamnya disesuaikan ke bulan/tahun sekarang.
     *
     * Deteksi tahun/bulan pakai regex terhadap 4-digit & 2-digit di ujung
     * string, BUKAN explode('/') + hitung index segmen — karena format nomor
     * memo yang beneran dipakai ternyata gak konsisten jumlah segmennya
     * (mis. format lama "323/LS.D1.PPK/KU.00/07/2026" vs format baru
     * "374.KU.00.00/2027"). Pendekatan lama (explode + index) cuma cocok utk
     * format lama, jadi di format baru bulan/tahunnya gak pernah ke-update
     * dan nyangkut ke tahun manapun yang kebetulan jadi contoh pertama.
     */
    private static function templateEkor(): string
    {
        $bulan = now()->format('m');
        $tahun = now()->format('Y');

        $contoh = self::contohTerakhir();

        if (!$contoh) {
            // Belum pernah ada nomor memo sama sekali -> template default kamu
            return ".KU.00.00/{$tahun}";
        }

        // Buang angka urut di depan, sisanya dipakai sbg "ekor" format.
        $ekor = preg_replace('/^\s*\d+/', '', $contoh);

        // Ganti 4 digit tahun di paling ujung ke tahun sekarang — gak
        // bergantung separator apapun (baik "/" maupun "."), jadi format lama
        // ("323/.../07/2026") maupun format baru (".KU.00.00/2027") sama-sama
        // ke-update tahunnya.
        $ekor = preg_replace('/\d{4}\s*$/', $tahun, $ekor);

        // Kalau formatnya juga punya segmen bulan terpisah (misal ".../07/2026"),
        // segmen bulan (2 digit sebelum tahun, dipisah "/") ikut di-update juga.
        $ekor = preg_replace('/(\/)\d{2}(\/\d{4}\s*$)/', '$1' . $bulan . '$2', $ekor);

        return $ekor;
    }

    /**
     * Teks nomor memo lengkap dengan nomor_urut TERBESAR yang pernah dipakai
     * (dari kedua sumber), dipakai sebagai acuan format "ekor".
     */
    private static function contohTerakhir(): ?string
    {
        $entryTerakhir = MemoEntry::orderByDesc('nomor_urut')->first()?->nomor_memo;

        $agendaTerakhir = self::semuaNomorDariAgenda()
            ->sortByDesc(fn($n) => self::ekstrakUrutan($n))
            ->first();

        return collect([$entryTerakhir, $agendaTerakhir])
            ->filter()
            ->sortByDesc(fn($n) => self::ekstrakUrutan($n))
            ->first();
    }
}