<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\MemoEntry;

/**
 * Sumber tunggal angka outstanding per MAK.
 *
 * Outstanding = total nominal memo berstatus "outstanding" (belum diinput di SAKTI),
 * dari SEMUA unit, dikelompokkan per kode MAK. Aturannya sama persis dengan
 * MonitoringMemoController::semuaMemo() dan kondisiMak():
 * - memo perdin: satu baris per nomor memo PNS / Non PNS yang terisi
 * - memo entry: satu baris per entry
 * - status kosong dianggap "outstanding"
 * - memo tanpa MAK tidak dihitung
 * - memo lama (is_legacy = true, pra-outstanding) tidak dihitung
 *
 * Dipakai oleh halaman Anggaran (kolom Outstanding) dan Monitoring Memo.
 */
class MakOutstandingService
{
    /**
     * @return array<string,int> [kode MAK (apa adanya, sudah di-trim) => total rupiah]
     */
    public static function perMak(): array
    {
        $hasil = [];

        $tambah = function ($mak, $nominal, $status) use (&$hasil) {
            $mak = trim((string) $mak);
            if ($mak === '' || ($status ?: 'outstanding') !== 'outstanding') {
                return;
            }
            $hasil[$mak] = ($hasil[$mak] ?? 0) + (int) $nominal;
        };

        foreach (Agenda::query()->withoutGlobalScope('unit')->where('is_legacy', false)->cursor() as $a) {
            if (filled($a->nomor_memo_pns)) {
                $tambah($a->mak, $a->nominal_memo_pns ?? $a->biaya_asn, $a->status_pns);
            }
            if (filled($a->nomor_memo_non_pns)) {
                $tambah($a->mak, $a->nominal_memo_non_pns ?? $a->biaya_non_asn, $a->status_non_pns);
            }
        }

        foreach (MemoEntry::query()->withoutGlobalScope('unit')->where('is_legacy', false)->cursor() as $e) {
            $tambah($e->mak, $e->nominal, $e->status_anggaran);
        }

        return $hasil;
    }
}