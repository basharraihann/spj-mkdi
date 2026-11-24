<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agenda;
use App\Models\MemoEntry;
use App\Services\MemoTahapService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Halaman monitoring memo: progres per unit, antrean kerja (outstanding, SAKTI, SP2D),
 * dan riwayat lengkap tiap memo.
 *
 * Admin dan TU melihat memo semua unit. Akun lain hanya melihat unitnya sendiri.
 * Memo lama (is_legacy = true, dibuat sebelum fitur outstanding) tidak ikut dibaca.
 *
 * Angka outstanding per MAK untuk halaman Anggaran ada di App\Services\MakOutstandingService.
 */
class MonitoringMemoController extends Controller
{
    /**
     * Siapa yang boleh melihat memo semua unit.
     * Sesuaikan dengan cara aplikasi membedakan role TU.
     */
    private function lihatSemuaUnit(): bool
    {
        $user = auth()->user();

        // TU Sekretariat melihat semua unit. 'tu' = role lama, dipertahankan sampai user lama dimigrasi.
        return (bool) ($user?->isAdmin() || $user?->isTuSekretariat() || $user?->role === User::ROLE_TU);
    }

    public function index()
    {
        // Memo yang boleh dilihat user ini. Admin dan TU: semua unit.
        $memos = $this->semuaMemo($this->lihatSemuaUnit());

        $outstanding = $memos->where('status', 'outstanding');
        $sakti = $memos->where('status', 'sakti');
        $sp2dBulanIni = $memos->where('status', 'sp2d')->filter(
            fn($m) => $m['tgl_sp2d'] && $m['tgl_sp2d']->isSameMonth(now())
        );

        $unitRingkasan = $this->ringkasanUnit($memos);

        $antreanOutstanding = $outstanding->sortByDesc('umur')->values();
        $antreanSakti = $sakti->sortByDesc('umur')->values();
        $antreanSp2d = $sp2dBulanIni->sortByDesc('tgl_sp2d')->values();

        $logPerMemo = $this->logPerMemo($memos);

        return view('monitoring.index', compact(
            'unitRingkasan',
            'antreanOutstanding',
            'antreanSakti',
            'antreanSp2d',
            'logPerMemo'
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

    /**
     * Progres per unit: berapa memo yang masih outstanding, sudah SAKTI, dan sudah SP2D.
     * Memo batal tidak dihitung. Unit kosong dikelompokkan ke kunci '__none'.
     */
    private function ringkasanUnit($memos)
    {
        return $memos
            ->where('status', '!=', 'batal')
            ->filter(fn($m) => $m['status'] !== 'sp2d'
                || ($m['tgl_sp2d'] && $m['tgl_sp2d']->isSameMonth(now())))
            ->groupBy(fn($m) => $m['unit'] ?: '__none')
            ->map(function ($grup, $unit) {
                $o = $grup->where('status', 'outstanding');
                $s = $grup->where('status', 'sakti');
                $p = $grup->where('status', 'sp2d');
                $jumlah = $grup->count();

                return [
                    'unit' => $unit,
                    'label' => $unit === '__none' ? 'Tanpa unit' : $unit,
                    'jumlah' => $jumlah,
                    'total' => (int) $grup->sum('nominal'),
                    'outstanding_jumlah' => $o->count(),
                    'outstanding_total' => (int) $o->sum('nominal'),
                    'sakti_jumlah' => $s->count(),
                    'sakti_total' => (int) $s->sum('nominal'),
                    'sp2d_jumlah' => $p->count(),
                    'sp2d_total' => (int) $p->sum('nominal'),
                    'persen_sp2d' => $jumlah > 0 ? (int) round($p->count() / $jumlah * 100) : 0,
                    'lama' => $o->where('umur', '>', 30)->count(),
                ];
            })
            ->sortByDesc('outstanding_total')
            ->values();
    }

    public function semuaMemo(bool $lintasUnit = false)
    {
        $rows = collect();

        // Memo lama (pra-outstanding) tidak ikut dipantau.
        $agendas = Agenda::query()->where('is_legacy', false);
        $entries = MemoEntry::query()->where('is_legacy', false);

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

        $peta = MemoTahapService::petaTahap($rows->pluck('status', 'id')->all());
        $pengajuan = $this->petaPengajuan($rows->pluck('id')->all());

        return $rows->map(fn($r) => array_merge($r, [
            'tahap' => $peta[$r['id']] ?? MemoTahapService::dariStatus($r['status']),
            'pengajuan' => $pengajuan[$r['id']] ?? null,
        ]))->values();
    }

    /**
     * Pengajuan pengembalian yang masih menunggu keputusan, dikunci dengan id memo.
     */
    private function petaPengajuan(array $ids): array
    {
        return DB::table('memo_pengajuan_kembali')
            ->where('status', 'menunggu')
            ->whereIn('memo_key', $ids)
            ->get()
            ->mapWithKeys(fn($p) => [$p->memo_key => ['id' => $p->id, 'alasan' => $p->alasan]])
            ->all();
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
            'tahap' => 'draft', // diisi ulang di semuaMemo()
            'tgl_memo' => $tglMemo,
            'tgl_sakti' => $tglSakti,
            'tgl_sp2d' => $tglSp2d,
            'umur' => max($umur, 0),
        ];
    }

    /**
     * Riwayat lengkap per memo (tanpa limit), terbaru di atas.
     * Gabungan: perubahan status anggaran, pengiriman, verifikasi K2/K3, dan pengajuan pengembalian.
     * Format item: nomor_memo, judul, catatan, waktu (Carbon), oleh.
     */
    private function logPerMemo($memos)
    {
        $peta = $memos->keyBy('id');   // hanya memo yang boleh dilihat user ini
        $keys = $peta->keys()->all();

        if ($keys === []) {
            return collect();
        }

        // 1) Perubahan status anggaran
        $logStatus = DB::table('memo_status_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as user_name')
            ->get()
            ->map(function ($log) use ($peta) {
                $key = $log->memo_type === 'entry'
                    ? "entry-{$log->memo_id}"
                    : "agenda-{$log->memo_id}-{$log->jenis}";

                if (!$peta->has($key)) {
                    return null;
                }

                return [
                    'nomor_memo' => $peta[$key]['nomor_memo'],
                    'judul' => 'Status anggaran: ' . $log->dari . ' → ' . $log->ke,
                    'catatan' => $log->alasan,
                    'waktu' => Carbon::parse($log->created_at),
                    'oleh' => $log->user_name,
                ];
            })
            ->filter();

        // 2) Dikirim ke Keuangan
        $logKirim = DB::table('memo_progres as p')
            ->leftJoin('users as u', 'u.id', '=', 'p.dikirim_oleh')
            ->whereIn('p.memo_key', $keys)
            ->whereNotNull('p.dikirim_at')
            ->select('p.memo_key', 'p.dikirim_at', 'u.name as user_name')
            ->get()
            ->map(fn($r) => [
                'nomor_memo' => $peta[$r->memo_key]['nomor_memo'],
                'judul' => 'Dikirim ke Keuangan',
                'catatan' => null,
                'waktu' => Carbon::parse($r->dikirim_at),
                'oleh' => $r->user_name,
            ]);

        // 3) Verifikasi K2 / K3
        $logVerifikasi = DB::table('memo_verifikasi_logs as v')
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->whereIn('v.memo_key', $keys)
            ->select('v.*', 'u.name as user_name')
            ->get()
            ->map(fn($r) => [
                'nomor_memo' => $peta[$r->memo_key]['nomor_memo'],
                'judul' => "Verifikasi {$r->gerbang}: " . ($r->lengkap_sesuai ? 'lolos' : 'tidak lolos'),
                'catatan' => $r->lengkap_sesuai ? null : $r->alasan,
                'waktu' => Carbon::parse($r->created_at),
                'oleh' => $r->user_name,
            ]);

        // 4) Pengajuan pengembalian: saat diajukan, dan saat diputuskan
        $pengajuan = DB::table('memo_pengajuan_kembali as k')
            ->leftJoin('users as u', 'u.id', '=', 'k.diputus_oleh')
            ->whereIn('k.memo_key', $keys)
            ->select('k.*', 'u.name as putus_nama')
            ->get();

        $logPengajuan = $pengajuan->map(fn($r) => [
            'nomor_memo' => $peta[$r->memo_key]['nomor_memo'],
            'judul' => 'Pengajuan pengembalian diajukan',
            'catatan' => $r->alasan,
            'waktu' => Carbon::parse($r->created_at),
            'oleh' => null, // isi kalau tabelnya punya kolom pengaju, mis. $r->diajukan_oleh
        ]);

        $logPutusan = $pengajuan
            ->filter(fn($r) => $r->status !== 'menunggu' && $r->diputus_at)
            ->map(fn($r) => [
                'nomor_memo' => $peta[$r->memo_key]['nomor_memo'],
                'judul' => 'Pengajuan pengembalian ' . ($r->status === 'disetujui' ? 'disetujui' : 'ditolak'),
                'catatan' => $r->catatan_putusan,
                'waktu' => Carbon::parse($r->diputus_at),
                'oleh' => $r->putus_nama ?? 'Otomatis',
            ]);

        // 5) Perpindahan posisi berkas fisik (tabel terpisah, tidak terkait tahap memo)
        $logBerkas = DB::table('memo_berkas_fisik')
            ->whereIn('memo_key', $keys)
            ->orderBy('id')
            ->get()
            ->groupBy('memo_key')
            ->flatMap(function ($grup, $key) use ($peta) {
                $sebelumnya = null;

                return $grup->map(function ($r) use ($peta, $key, &$sebelumnya) {
                    $judul = $sebelumnya
                        ? "Berkas fisik: {$sebelumnya} → {$r->posisi}"
                        : "Berkas fisik: diatur ke {$r->posisi}";
                    $sebelumnya = $r->posisi;

                    return [
                        'nomor_memo' => $peta[$key]['nomor_memo'],
                        'judul' => $judul,
                        'catatan' => null,
                        'waktu' => Carbon::parse($r->created_at),
                        'oleh' => $r->oleh,
                    ];
                });
            });

        return $logStatus
            ->concat($logKirim)
            ->concat($logVerifikasi)
            ->concat($logPengajuan)
            ->concat($logPutusan)
            ->concat($logBerkas)
            ->groupBy('nomor_memo')
            ->map(fn($g) => $g->sortByDesc('waktu')->values());
    }
}