<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\MemoTahapService;

/**
 * Halaman monitoring memo & anggaran:
 * ringkasan, kondisi per MAK, antrean kerja (outstanding & SAKTI), dan riwayat perubahan status.
 *
 * Pembagian data:
 * - Daftar memo, antrean, ringkasan, dan riwayat mengikuti scope unit
 *   (non-admin hanya melihat unitnya sendiri, admin melihat semua).
 * - Angka sisa MAK (outstanding dan sisa efektif) dihitung dari SEMUA unit, karena
 *   pagu MAK satu kantong uang. Yang keluar ke tampilan hanya angkanya, tidak ada
 *   uraian atau nomor memo unit lain.
 */
class MonitoringMemoController extends Controller
{
    /** Batas persentase terpakai untuk status "perlu perhatian". */
    private const AMBANG_PERHATIAN = 90;

    public function index()
    {
        // Memo yang boleh dilihat user ini (scope unit aktif untuk non-admin)
        $memos = $this->semuaMemo();

        // Memo semua unit, hanya untuk menghitung sisa MAK. Admin sudah melihat semua.
        $memoSemuaUnit = auth()->user()?->isAdmin() ? $memos : $this->semuaMemo(true);

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

        $maks = $this->kondisiMak($memos, $memoSemuaUnit);

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

        $userId = $request->user()?->id;
        $berhasil = 0;
        $dilewati = 0;

        foreach ($data['ids'] as $id) {
            try {
                if ($data['status'] === 'sakti') {
                    // Hanya memo yang sudah lolos K2 (tahap lolos_spk).
                    MemoTahapService::inputSakti($id, $data['tanggal'] ?? null, $userId);
                    $berhasil++;
                } else {
                    MemoTahapService::ubahStatusManual($id, 'sp2d', $data['tanggal'] ?? null, null, $userId)
                        ? $berhasil++ : $dilewati++;
                }
            } catch (\DomainException | ModelNotFoundException $e) {
                $dilewati++;
            }
        }

        $pesan = "{$berhasil} memo ditandai " . strtoupper($data['status']) . '.';
        if ($dilewati > 0) {
            $pesan .= " {$dilewati} dilewati (belum pada tahap yang sesuai atau statusnya sudah berubah).";
        }

        return redirect()->route('monitoring.index')->with('success', $pesan);
    }
    private function kondisiMak($memos, $memoSemuaUnit)
    {
        // Outstanding per MAK dari SEMUA unit. Kode MAK dibersihkan (trim) supaya
        // pencocokan tidak meleset karena spasi.
        $outMap = $memoSemuaUnit
            ->where('status', 'outstanding')
            ->filter(fn($m) => filled($m['mak']))
            ->groupBy(fn($m) => trim($m['mak']))
            ->map(fn($grup) => (int) $grup->sum('nominal'))
            ->all();

        // Daftar MAK yang tampil: hanya yang dipakai memo yang boleh dilihat user ini.
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
                    'dipakai' => $grup->isNotEmpty(),
                ];
            })
            // MAK yang pernah dipakai di atas; di dalamnya yang paling tipis lebih dulu
            ->sortBy(fn($m) => [$m['dipakai'] ? 0 : 1, $m['efektif'] ?? 0, $m['mak']])
            ->values();
    }

    /**
     * Gabungan seluruh memo (perdin PNS/Non PNS + memo mandiri), semua status.
     * Nominal perdin memakai snapshot kalau ada.
     *
     * Default mengikuti scope unit user. $lintasUnit = true melepas scope itu,
     * hanya dipakai untuk menghitung sisa MAK.
     */
    private function semuaMemo(bool $lintasUnit = false)
    {
        $rows = collect();

        $agendas = Agenda::query();
        $entries = MemoEntry::query();

        if ($lintasUnit) {
            $agendas->withoutGlobalScope('unit');
            $entries->withoutGlobalScope('unit');
        }

        foreach ($agendas->with(['pic', 'penanggungJawab', 'pegawai', 'unit'])->get() as $a) {
            $pic = $a->pic->nama_gelar ?? $a->pic->nama
                ?? $a->penanggungJawab->nama_gelar ?? $a->penanggungJawab->nama ?? '-';
            $unit = $a->unit?->kode;

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
                    $a->tgl_sp2d_pns,
                    $unit,
                    'perdin'
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
                    $a->tgl_sp2d_non_pns,
                    $unit,
                    'perdin'
                ));
            }
        }

        foreach ($entries->with(['pic', 'unit'])->get() as $e) {
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
                $e->tgl_sp2d,
                $e->unit?->kode,
                $e->jenis_memo
            ));
        }

        return $rows;
    }

    private function baris($id, $nomor, $label, $jenisLabel, $uraian, $pic, $mak, $nominal, $status, $tglMemo, $tglSakti, $tglSp2d, $unit = null, $jenis = null): array
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
            'jenis' => $jenis,
            'jenis_label' => $jenisLabel,
            'unit' => $unit,
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

    /**
     * 25 perubahan status terakhir, lengkap dengan nama user dan nomor memonya.
     * Non-admin hanya melihat perubahan pada memo yang boleh dilihatnya (unitnya sendiri).
     */
    private function riwayat($memos)
    {
        $peta = $memos->keyBy('id');
        $admin = (bool) auth()->user()?->isAdmin();

        return DB::table('memo_status_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as user_name')
            ->orderByDesc('l.id')
            ->limit($admin ? 25 : 300)
            ->get()
            ->map(function ($log) use ($peta) {
                $key = $log->memo_type === 'entry'
                    ? "entry-{$log->memo_id}"
                    : "agenda-{$log->memo_id}-{$log->jenis}";

                $log->milik_user = $peta->has($key);
                $log->nomor_memo = $peta[$key]['nomor_memo'] ?? $key;
                $log->waktu = Carbon::parse($log->created_at);

                return $log;
            })
            ->when(!$admin, fn($c) => $c->filter(fn($log) => $log->milik_user))
            ->take(25)
            ->values();
    }
}