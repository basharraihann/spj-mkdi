<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;

class MakSisaService
{
    /**
     * Total nominal per MAK dari memo yang statusnya masih "outstanding"
     * (belum diinput di SAKTI). Master MAK ditarik dari SAKTI, jadi memo berstatus
     * sakti / sp2d sudah ada di realisasi dan tidak dihitung lagi. Memo batal juga tidak.
     *
     * $semuaUnit = true  -> hitung dari SEMUA unit (scope unit dilepas)
     * $semuaUnit = false -> hanya unit user yang login (perilaku lama)
     *
     * @return array<string,int>
     */
    public static function outstanding(
        ?int $kecualiEntryId = null,
        ?int $kecualiAgendaId = null,
        bool $semuaUnit = false
    ): array {
        $entry = MemoEntry::query()
            ->when($semuaUnit, fn($q) => $q->withoutGlobalScope('unit'))->where('status_anggaran', 'outstanding')
            ->whereNotNull('mak')
            ->when($kecualiEntryId, fn($q) => $q->where('id', '!=', $kecualiEntryId))
            ->selectRaw('mak, SUM(nominal) as total')
            ->groupBy('mak')
            ->pluck('total', 'mak');

        $agenda = Agenda::query()
            ->when($semuaUnit, fn($q) => $q->withoutGlobalScope('unit'))->with('pegawai')
            ->whereNotNull('mak')
            ->where(function ($q) {
                $q->where(function ($w) {
                    $w->where('status_pns', 'outstanding')
                        ->whereNotNull('nomor_memo_pns')
                        ->where('nomor_memo_pns', '!=', '');
                })->orWhere(function ($w) {
                    $w->where('status_non_pns', 'outstanding')
                        ->whereNotNull('nomor_memo_non_pns')
                        ->where('nomor_memo_non_pns', '!=', '');
                });
            })
            ->when($kecualiAgendaId, fn($q) => $q->where('id', '!=', $kecualiAgendaId))
            ->get()
            ->groupBy('mak')
            ->map(fn($rows) => (int) $rows->sum(fn($a) => self::nominalAgendaOutstanding($a)));

        $hasil = [];
        foreach ([$entry, $agenda] as $sumber) {
            foreach ($sumber as $mak => $total) {
                $hasil[$mak] = ($hasil[$mak] ?? 0) + (int) $total;
            }
        }

        return $hasil;
    }

    /**
     * Outstanding per MAK, dipisah: unit user sendiri vs unit lain.
     * Yang dikurangkan dari sisa anggaran = sendiri + lain.
     *
     * @return array<string, array{sendiri:int, lain:int}>
     */
    public static function outstandingDetail(?int $kecualiEntryId = null, ?int $kecualiAgendaId = null): array
    {
        $sendiri = self::outstanding($kecualiEntryId, $kecualiAgendaId, false);
        $semua = self::outstanding($kecualiEntryId, $kecualiAgendaId, true);

        $hasil = [];
        foreach ($semua as $mak => $total) {
            $s = (int) ($sendiri[$mak] ?? 0);
            $hasil[$mak] = ['sendiri' => $s, 'lain' => max((int) $total - $s, 0)];
        }

        return $hasil;
    }

    public static function sisaEfektif(?int $kecualiEntryId = null): array
    {
        $out = self::outstanding($kecualiEntryId, null, true);

        return MakOption::get(['mak', 'sisa'])
            ->mapWithKeys(fn($o) => [$o->mak => (int) $o->sisa - ($out[$o->mak] ?? 0)])
            ->all();
    }

    public static function cekNominal(string $mak, int $nominal, ?int $kecualiEntryId = null): ?string
    {
        $opt = MakOption::where('mak', $mak)->first();
        if (!$opt) {
            return null; // MAK manual, tidak bisa dicek
        }

        $efektif = (int) $opt->sisa - (self::outstanding($kecualiEntryId, null, true)[$mak] ?? 0);

        return $nominal <= $efektif
            ? null
            : 'Nominal (Rp ' . number_format($nominal, 0, ',', '.') . ') melebihi sisa MAK '
            . $mak . ' (Rp ' . number_format(max($efektif, 0), 0, ',', '.') . ').';
    }

    /**
     * Nominal memo agenda yang masih outstanding.
     * Pakai snapshot (nominal_memo_*) kalau ada; kalau belum, hitung dari rincian peserta.
     */
    private static function nominalAgendaOutstanding(Agenda $a): int
    {
        $total = 0;

        if (filled($a->nomor_memo_pns) && $a->status_pns === 'outstanding') {
            $total += (int) ($a->nominal_memo_pns ?? $a->biaya_asn);
        }

        if (filled($a->nomor_memo_non_pns) && $a->status_non_pns === 'outstanding') {
            $total += (int) ($a->nominal_memo_non_pns ?? $a->biaya_non_asn);
        }

        return $total;
    }

    /**
     * Return pesan error kalau total biaya agenda > sisa efektif MAK, selain itu null.
     * MAK yang tidak ada di master (ketik manual) tidak bisa dicek -> null.
     */
    public static function cek(Agenda $agenda, string $mak): ?string
    {
        $opt = MakOption::where('mak', $mak)->first();
        if (!$opt) {
            return null;
        }

        $agenda->loadMissing('pegawai');
        $total = (int) ((float) $agenda->biaya_asn + (float) $agenda->biaya_non_asn);
        $lain = self::outstanding(null, $agenda->id, true)[$mak] ?? 0; // true = semua unit
        $efektif = (int) $opt->sisa - $lain;

        if ($total <= $efektif) {
            return null;
        }

        return 'Total biaya agenda (Rp ' . number_format($total, 0, ',', '.') . ') melebihi sisa MAK '
            . $mak . ' (Rp ' . number_format(max($efektif, 0), 0, ',', '.') . '). Pilih MAK lain.';
    }
}