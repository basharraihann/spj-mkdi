<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\AnggaranItem;
use App\Models\AnggaranUpload;
use App\Models\MakOption;
use App\Models\MemoEntry;
use App\Services\AnggaranUnitResolver;
use App\Services\MemoTahapService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Halaman monitoring memo & anggaran:
 * ringkasan, progres per unit, kondisi per MAK, antrean kerja (outstanding, SAKTI, SP2D),
 * dan riwayat perubahan status.
 *
 * Pembagian data:
 * - Admin dan TU melihat memo semua unit. Akun lain hanya melihat unitnya sendiri.
 * - Angka sisa MAK (outstanding dan sisa efektif) selalu dihitung dari SEMUA unit, karena
 *   pagu MAK satu kantong uang. Untuk akun yang hanya melihat unitnya, yang keluar ke
 *   tampilan hanya angkanya, tidak ada uraian atau nomor memo unit lain.
 */
class MonitoringMemoController extends Controller
{
    /** Batas persentase terpakai untuk status "perlu perhatian". */
    private const AMBANG_PERHATIAN = 90;

    /**
     * Siapa yang boleh melihat memo semua unit.
     * Sesuaikan dengan cara aplikasi membedakan role TU.
     */
    private function lihatSemuaUnit(): bool
    {
        $user = auth()->user();

        return (bool) ($user?->isAdmin() || $user?->role === 'tu');
    }

    public function index()
    {
        $lihatSemua = $this->lihatSemuaUnit();

        // Memo yang boleh dilihat user ini. Admin dan TU: semua unit.
        $memos = $this->semuaMemo($lihatSemua);

        // Memo semua unit, hanya untuk menghitung sisa MAK.
        $memoSemuaUnit = $lihatSemua ? $memos : $this->semuaMemo(true);

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

        // Peta dari data anggaran aktif: Kegiatan -> Program, dan MAK -> unit pemilik anggaran
        $petaAnggaran = $this->petaAnggaran();
        $kegProgram = $petaAnggaran['kegProgram'];
        $uraianLevel = $petaAnggaran['uraian'];

        $maks = $this->kondisiMak($memos, $memoSemuaUnit, $petaAnggaran);
        $unitRingkasan = $this->ringkasanUnit($memos);

        $antreanOutstanding = $outstanding->sortByDesc('umur')->values();
        $antreanSakti = $sakti->sortByDesc('umur')->values();
        $antreanSp2d = $sp2dBulanIni->sortByDesc('tgl_sp2d')->values();

        $riwayat = $this->riwayat($memos, $lihatSemua);
        $logPerMemo = $this->logPerMemo($memos);

        return view('monitoring.index', compact(
            'ringkasan',
            'maks',
            'kegProgram',
            'uraianLevel',
            'unitRingkasan',
            'antreanOutstanding',
            'antreanSakti',
            'antreanSp2d',
            'riwayat',
            'logPerMemo',
            'tarikanTanggal',
            'lihatSemua'
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
     * Peta dari data anggaran aktif (upload terakhir), semuanya dikunci dengan kode MAK
     * yang sudah dinormalkan (tanpa awalan program, komponen 3 digit), lihat kunciMak():
     * - kegProgram: kode kegiatan => kode program, mis. ['7458' => 'CL'].
     * - pemilik: MAK => unit pemilik anggaran (AnggaranUnitResolver, sama dengan kolom Unit
     *   di halaman Anggaran). Ini unit pemilik pagu, bukan unit pembuat memo.
     * - realisasi: MAK => ['lalu' => ..., 'ini' => ...] dijumlah dari item anggaran.
     * - uraian: uraian Kegiatan, RO, Komponen, Sub Komponen untuk baris induk, dikunci jalur
     *   (keg, keg|ro, keg|ro|komp, keg|ro|komp|sub) karena kode komponen tidak unik antar RO.
     */
    private function petaAnggaran(): array
    {
        $hasil = [
            'kegProgram' => [],
            'pemilik' => [],
            'realisasi' => [],
            'item' => [],
            'uraian' => ['keg' => [], 'ro' => [], 'komp' => [], 'sub' => []],
        ];

        $aktif = AnggaranUpload::where('is_active', true)->latest('id')->first();
        if (!$aktif) {
            return $hasil;
        }

        $resolver = app(AnggaranUnitResolver::class);
        $cacheUnit = [];   // pemetaan unit ditentukan per RO, jadi cukup dihitung sekali per RO

        foreach (AnggaranItem::where('anggaran_upload_id', $aktif->id)->orderBy('id')->cursor() as $i) {
            $keg = trim((string) $i->keg_kode);
            $kegTanpaProg = $keg;
            if (str_contains($keg, '.')) {
                [$prog, $kegTanpaProg] = explode('.', $keg, 2);
                $hasil['kegProgram'][trim($kegTanpaProg)] = trim($prog);
            }
            $kegTanpaProg = trim($kegTanpaProg);

            $ro = trim((string) $i->ro_kode);
            $komp = self::padKomp(trim((string) $i->komp_kode));
            // sub_kode di data anggaran berbentuk "051.0A" (komponen.sub); di kode MAK hanya "0A"
            $sub = trim((string) $i->sub_kode);
            if (str_contains($sub, '.')) {
                $sub = substr($sub, strrpos($sub, '.') + 1);
            }
            $akun = trim((string) $i->akun_kode);

            $kRo = $kegTanpaProg . '|' . $ro;
            $kKomp = $kRo . '|' . $komp;
            $kSub = $kKomp . '|' . $sub;
            $hasil['uraian']['keg'][$kegTanpaProg] ??= $i->keg_uraian;
            $hasil['uraian']['ro'][$kRo] ??= $i->ro_uraian;
            $hasil['uraian']['komp'][$kKomp] ??= $i->komp_uraian;
            $hasil['uraian']['sub'][$kSub] ??= $i->sub_uraian;

            $mak = implode('.', [$kegTanpaProg, $ro, $komp, $sub, $akun]);

            $roKey = $keg . '|' . $ro;
            if (!array_key_exists($roKey, $cacheUnit)) {
                $r = $resolver->resolve($i);
                $cacheUnit[$roKey] = $r['unit'] ?? ($r['induk'] ?? null);
            }
            if ($cacheUnit[$roKey]) {
                $hasil['pemilik'][$mak] ??= $cacheUnit[$roKey];
            }

            $r = $hasil['realisasi'][$mak] ?? ['lalu' => null, 'ini' => null];
            if ($i->realisasi_lalu !== null) {
                $r['lalu'] = ($r['lalu'] ?? 0) + (int) $i->realisasi_lalu;
            }
            if ($i->realisasi_ini !== null) {
                $r['ini'] = ($r['ini'] ?? 0) + (int) $i->realisasi_ini;
            }
            $hasil['realisasi'][$mak] = $r;

            // Rincian item di bawah akun (level terdalam, sama seperti halaman Anggaran)
            $lalu = $i->realisasi_lalu !== null ? (int) $i->realisasi_lalu : null;
            $ini = $i->realisasi_ini !== null ? (int) $i->realisasi_ini : null;
            $hasil['item'][$mak][] = [
                'kode' => trim((string) $i->item_kode),
                'uraian' => $i->item_uraian,
                'pagu' => (int) $i->pagu,
                'lalu' => $lalu,
                'ini' => $ini,
                'real' => $i->realisasi !== null ? (int) $i->realisasi : (($lalu ?? 0) + ($ini ?? 0)),
            ];
        }

        return $hasil;
    }

    private static function padKomp(string $k): string
    {
        return (ctype_digit($k) && strlen($k) < 3) ? str_pad($k, 3, '0', STR_PAD_LEFT) : $k;
    }

    /** Samakan bentuk kode MAK: buang awalan program (7 bagian jadi 6) dan isi nol komponen. */
    private static function kunciMak(string $kode): string
    {
        $p = explode('.', trim($kode));
        if (count($p) === 7) {
            array_shift($p);
        }
        if (count($p) === 6) {
            $p[3] = self::padKomp($p[3]);
        }

        return implode('.', $p);
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

    private function kondisiMak($memos, $memoSemuaUnit, array $peta = [])
    {
        // Outstanding per MAK dari SEMUA unit. Kode MAK dibersihkan (trim) supaya
        // pencocokan tidak meleset karena spasi.
        $outMap = $memoSemuaUnit
            ->where('status', 'outstanding')
            ->filter(fn($m) => filled($m['mak']))
            ->groupBy(fn($m) => trim($m['mak']))
            ->map(fn($grup) => (int) $grup->sum('nominal'))
            ->all();

        // Outstanding per MAK per unit, hanya dari memo yang boleh dilihat user ini.
        $outUnitMap = $memos
            ->where('status', 'outstanding')
            ->filter(fn($m) => filled($m['mak']))
            ->groupBy(fn($m) => trim($m['mak']))
            ->map(fn($grup) => $grup
                ->groupBy(fn($m) => $m['unit'] ?: '__none')
                ->map(fn($g) => (int) $g->sum('nominal'))
                ->all())
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
            ->map(function ($kode) use ($master, $perMak, $outMap, $outUnitMap, $peta) {
                $o = $master->get($kode);
                $kk = self::kunciMak($kode);
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
                    'out_unit' => $outUnitMap[$kode] ?? [],
                    // Unit pemakai memo (bukan pemilik anggaran)
                    'unit_list' => $grup->pluck('unit')->map(fn($u) => $u ?: '__none')->unique()->sort()->values()->all(),
                    // Unit pemilik anggaran, dari pemetaan RO di data anggaran
                    'unit_pemilik' => $peta['pemilik'][$kk] ?? null,
                    // Realisasi bulan lalu dan bulan ini, dari item anggaran (null bila tidak ada)
                    'real_lalu' => $peta['realisasi'][$kk]['lalu'] ?? null,
                    'real_ini' => $peta['realisasi'][$kk]['ini'] ?? null,
                    // Item anggaran di bawah akun ini
                    'items' => $peta['item'][$kk] ?? [],
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
     * 25 perubahan status terakhir, lengkap dengan nama user dan nomor memonya.
     * Akun yang tidak boleh melihat semua unit hanya melihat perubahan pada memo unitnya sendiri.
     */
    private function riwayat($memos, bool $lihatSemua)
    {
        $peta = $memos->keyBy('id');

        return DB::table('memo_status_logs as l')
            ->leftJoin('users as u', 'u.id', '=', 'l.user_id')
            ->select('l.*', 'u.name as user_name')
            ->orderByDesc('l.id')
            ->limit($lihatSemua ? 25 : 300)
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
            ->when(!$lihatSemua, fn($c) => $c->filter(fn($log) => $log->milik_user))
            ->take(25)
            ->values();
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

        return $logStatus
            ->concat($logKirim)
            ->concat($logVerifikasi)
            ->concat($logPengajuan)
            ->concat($logPutusan)
            ->groupBy('nomor_memo')
            ->map(fn($g) => $g->sortByDesc('waktu')->values());
    }
}