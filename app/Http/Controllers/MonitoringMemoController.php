<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;
use App\Services\MakSisaService;
use App\Services\MemoStatusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Halaman monitoring memo & anggaran:
 * ringkasan, kondisi per MAK, antrean kerja (outstanding & SAKTI), dan riwayat perubahan status.
 */
class MonitoringMemoController extends Controller
{
    /** Batas persentase terpakai untuk status "perlu perhatian". */
    private const AMBANG_PERHATIAN = 90;

    public function index()
    {
        $memos = $this->semuaMemo();

        // Tanggal tarikan SAKTI terakhir (master MAK diisi dari tarikan ini)
        $tarikan = MakOption::query()->max('per_tanggal');
        $tarikanTanggal = $tarikan ? Carbon::parse($tarikan)->startOfDay() : null;

        // Memo outstanding yang dibuat pada/sebelum tanggal tarikan kemungkinan sudah diinput di SAKTI
        $memos = $memos->map(function ($m) use ($tarikanTanggal) {
            $m['lewat_tarikan'] = $m['status'] === 'outstanding'
                && $tarikanTanggal
                && $m['tgl_memo']
                && $m['tgl_memo']->copy()->startOfDay()->lte($tarikanTanggal);

            return $m;
        });

        $outstanding = $memos->where('status', 'outstanding');
        $sakti = $memos->where('status', 'sakti');
        $sp2dBulanIni = $memos->where('status', 'sp2d')->filter(
            fn($m) => $m['tgl_sp2d'] && $m['tgl_sp2d']->isSameMonth(now())
        );

        $ringkasan = [
            'outstanding' => ['jumlah' => $outstanding->count(), 'total' => (int) $outstanding->sum('nominal')],
            'sakti' => ['jumlah' => $sakti->count(), 'total' => (int) $sakti->sum('nominal')],
            'sp2d' => ['jumlah' => $sp2dBulanIni->count(), 'total' => (int) $sp2dBulanIni->sum('nominal')],
            'lama' => [
                'jumlah' => $outstanding->where('umur', '>', 30)->count(),
                'total' => (int) $outstanding->where('umur', '>', 30)->sum('nominal')
            ],
        ];

        $maks = $this->kondisiMak($memos);

        $antreanOutstanding = $outstanding->sortByDesc('umur')->values();
        $antreanSakti = $sakti->sortByDesc('umur')->values();

        $riwayat = $this->riwayat($memos);

        return view('monitoring.index', compact(
            'ringkasan',
            'maks',
            'antreanOutstanding',
            'antreanSakti',
            'riwayat',
            'tarikanTanggal'
        ));
    }

    /**
     * Aksi massal: hanya perpindahan maju (outstanding -> sakti, sakti -> sp2d),
     * jadi tidak perlu alasan. Mundur / batal tetap lewat dropdown di daftar memo.
     */
    public function statusMassal(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['sakti', 'sp2d'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['string', 'regex:/^(entry-\d+|agenda-\d+-(pns|non_pns))$/'],
            'tanggal' => ['nullable', 'date'],
        ], [
            'ids.required' => 'Pilih minimal satu memo.',
        ]);

        $berhasil = 0;
        $dilewati = 0;

        foreach ($data['ids'] as $id) {
            try {
                MemoStatusService::ubah(
                    $id,
                    $data['status'],
                    $data['tanggal'] ?? null,
                    null,
                    $request->user()?->id
                ) ? $berhasil++ : $dilewati++;
            } catch (\DomainException | ModelNotFoundException $e) {
                $dilewati++;
            }
        }

        $pesan = "{$berhasil} memo ditandai " . strtoupper($data['status']) . '.';
        if ($dilewati > 0) {
            $pesan .= " {$dilewati} dilewati (statusnya sudah berubah).";
        }

        return redirect()->route('monitoring.index')->with('success', $pesan);
    }

    /**
     * Kondisi per MAK: gabungan master MAK aktif dan MAK yang pernah dipakai di memo.
     * Memo batal tidak dihitung. MAK yang diketik manual (tidak ada di master) ikut tampil
     * dengan status 'tanpa_master'. Sisa efektif = sisa master (tarikan SAKTI) - outstanding.
     */
    private function kondisiMak($memos)
    {
        // Kode MAK dibersihkan (trim) di semua sisi supaya pencocokan tidak meleset karena spasi
        $outMap = [];
        foreach (MakSisaService::outstanding() as $kode => $total) {
            $k = trim((string) $kode);
            $outMap[$k] = ($outMap[$k] ?? 0) + (int) $total;
        }

        $perMak = $memos
            ->where('status', '!=', 'batal')
            ->filter(fn($m) => filled($m['mak']))
            ->groupBy(fn($m) => trim($m['mak']));

        $master = MakOption::query()
            ->where('is_active', true)
            ->get()
            ->keyBy(fn($o) => trim($o->mak));

        return $master->keys()
            ->merge($perMak->keys())
            ->merge(array_keys($outMap))
            ->unique()
            ->map(function ($kode) use ($master, $perMak, $outMap) {
                $o = $master->get($kode);
                $grup = $perMak->get($kode, collect());
                $out = $outMap[$kode] ?? 0;

                $pagu = $o ? (int) $o->pagu : null;
                $realisasi = $o ? (int) $o->realisasi : null;
                $efektif = $o ? (int) $o->sisa - $out : null;
                $persen = $o && $pagu > 0 ? round((($realisasi + $out) / $pagu) * 100, 1) : null;

                if (!$o) {
                    $kondisi = 'tanpa_master';
                } elseif ($efektif < 0) {
                    $kondisi = 'minus';
                } elseif ($persen !== null && $persen >= self::AMBANG_PERHATIAN) {
                    $kondisi = 'perhatian';
                } else {
                    $kondisi = 'normal';
                }

                return [
                    'mak' => $kode,
                    'uraian' => $o?->uraian_belanja,
                    'master' => (bool) $o,
                    'pagu' => $pagu,
                    'realisasi' => $realisasi,
                    'outstanding' => $out,
                    'efektif' => $efektif,
                    'persen' => $persen,
                    'kondisi' => $kondisi,
                    'jumlah_memo' => $grup->count(),
                    'menunggu_sp2d' => $grup->where('status', 'sakti')->count(),
                    'memo_terakhir' => $grup->pluck('tgl_memo')->filter()->max(),
                    'dipakai' => $grup->isNotEmpty() || $out > 0,
                ];
            })
            // MAK yang pernah dipakai di atas; di dalamnya yang paling tipis lebih dulu
            ->sortBy(fn($m) => [$m['dipakai'] ? 0 : 1, $m['efektif'] ?? 0, $m['mak']])
            ->values();
    }

    /**
     * Gabungan seluruh memo (perdin PNS/Non PNS + memo mandiri), semua status.
     * Nominal perdin memakai snapshot kalau ada.
     */
    private function semuaMemo()
    {
        $rows = collect();

        foreach (Agenda::with(['pic', 'penanggungJawab', 'pegawai'])->get() as $a) {
            $pic = $a->pic->nama_gelar ?? $a->pic->nama
                ?? $a->penanggungJawab->nama_gelar ?? $a->penanggungJawab->nama ?? '-';

            if (filled($a->nomor_memo_pns)) {
                $rows->push($this->baris(
                    "agenda-{$a->id}-pns",
                    $a->nomor_memo_pns,
                    'PNS',
                    'Perjalanan Dinas',
                    $a->uraian_kegiatan,
                    $pic,
                    $a->mak,
                    (int) ($a->nominal_memo_pns ?? $a->biaya_asn),
                    $a->status_pns,
                    $a->created_at,
                    $a->tgl_sakti_pns,
                    $a->tgl_sp2d_pns
                ));
            }

            if (filled($a->nomor_memo_non_pns)) {
                $rows->push($this->baris(
                    "agenda-{$a->id}-non_pns",
                    $a->nomor_memo_non_pns,
                    'Non PNS',
                    'Perjalanan Dinas',
                    $a->uraian_kegiatan,
                    $pic,
                    $a->mak,
                    (int) ($a->nominal_memo_non_pns ?? $a->biaya_non_asn),
                    $a->status_non_pns,
                    $a->created_at,
                    $a->tgl_sakti_non_pns,
                    $a->tgl_sp2d_non_pns
                ));
            }
        }

        foreach (MemoEntry::with('pic')->get() as $e) {
            $rows->push($this->baris(
                "entry-{$e->id}",
                $e->nomor_memo,
                null,
                $e->jenis_label,
                $e->uraian_kegiatan,
                $e->pic->nama_gelar ?? $e->pic->nama ?? '-',
                $e->mak,
                (int) $e->nominal,
                $e->status_anggaran,
                $e->tanggal_memo,
                $e->tgl_sakti,
                $e->tgl_sp2d
            ));
        }

        return $rows;
    }

    private function baris($id, $nomor, $label, $jenis, $uraian, $pic, $mak, $nominal, $status, $tglMemo, $tglSakti, $tglSp2d): array
    {
        $status = $status ?: 'outstanding';
        $tglMemo = $tglMemo ? Carbon::parse($tglMemo) : null;
        $tglSakti = $tglSakti ? Carbon::parse($tglSakti) : null;
        $tglSp2d = $tglSp2d ? Carbon::parse($tglSp2d) : null;

        // Umur: outstanding dihitung sejak memo dibuat, SAKTI sejak tanggal SAKTI
        $acuan = $status === 'sakti' ? $tglSakti : $tglMemo;
        $umur = $acuan ? (int) $acuan->copy()->startOfDay()->diffInDays(now()->startOfDay()) : 0;

        return [
            'id' => $id,
            'nomor_memo' => $nomor,
            'label' => $label,
            'jenis_label' => $jenis,
            'uraian' => $uraian,
            'pic' => $pic,
            'mak' => $mak,
            'nominal' => $nominal,
            'status' => $status,
            'tgl_memo' => $tglMemo,
            'tgl_sakti' => $tglSakti,
            'tgl_sp2d' => $tglSp2d,
            'umur' => max($umur, 0),
        ];
    }

    /** 25 perubahan status terakhir, lengkap dengan nama user dan nomor memonya. */
    private function riwayat($memos)
    {
        $peta = $memos->keyBy('id');

        return DB::table('memo_status_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as user_name')
            ->orderByDesc('l.id')
            ->limit(25)
            ->get()
            ->map(function ($log) use ($peta) {
                $key = $log->memo_type === 'entry'
                    ? "entry-{$log->memo_id}"
                    : "agenda-{$log->memo_id}-{$log->jenis}";

                $log->nomor_memo = $peta[$key]['nomor_memo'] ?? $key;
                $log->waktu = Carbon::parse($log->created_at);

                return $log;
            });
    }
}