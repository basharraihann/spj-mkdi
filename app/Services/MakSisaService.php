<?php

namespace App\Services;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;

class MakSisaService
{
    /**
     * Total nominal per MAK dari memo/agenda yang "masuk" SETELAH tanggal laporan FA.
     * Untuk agenda, patokannya anggaran_at (saat MAK dipilih); agenda lama
     * yang belum punya anggaran_at jatuh ke created_at.
     *
     * @return array<string,int>
     */
    public static function outstanding(?int $kecualiEntryId = null, ?int $kecualiAgendaId = null): array
    {
        $cut = MakOption::query()->max('per_tanggal');
        if (!$cut) {
            return [];
        }

        $entry = MemoEntry::query()
            ->whereDate('tanggal_memo', '>', $cut)
            ->when($kecualiEntryId, fn($q) => $q->where('id', '!=', $kecualiEntryId))
            ->selectRaw('mak, SUM(nominal) as total')
            ->groupBy('mak')
            ->pluck('total', 'mak');

        try {
            $agenda = Agenda::query()
                ->with('pegawai')
                ->whereNotNull('mak')
                ->whereRaw('DATE(COALESCE(anggaran_at, created_at)) > ?', [$cut])
                ->when($kecualiAgendaId, fn($q) => $q->where('id', '!=', $kecualiAgendaId))
                ->get()
                ->groupBy('mak')
                ->map(fn($rows) => (int) $rows->sum(
                    fn($a) => (float) $a->biaya_asn + (float) $a->biaya_non_asn
                ));
        } catch (\Throwable $e) {
            report($e);
            $agenda = collect();
        }

        $hasil = [];
        foreach ([$entry, $agenda] as $sumber) {
            foreach ($sumber as $mak => $total) {
                $hasil[$mak] = ($hasil[$mak] ?? 0) + (int) $total;
            }
        }

        return $hasil;
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
        $lain = self::outstanding(null, $agenda->id)[$mak] ?? 0;
        $efektif = (int) $opt->sisa - $lain;

        if ($total <= $efektif) {
            return null;
        }

        return 'Total biaya agenda (Rp ' . number_format($total, 0, ',', '.') . ') melebihi sisa MAK '
            . $mak . ' (Rp ' . number_format(max($efektif, 0), 0, ',', '.') . '). Pilih MAK lain.';
    }
}