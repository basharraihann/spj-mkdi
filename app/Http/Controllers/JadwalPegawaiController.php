<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\DisposisiSurat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Tab "Jadwal Pegawai" di halaman Disposisi (?tampil=jadwal).
 * Berfungsi sebagai LOG AKTIVITAS pegawai: kegiatan yang berkaitan dengan dia
 * (hari ini, mendatang, yang sudah lewat, dan yang sudah ditandai selesai).
 *
 * Mode (parameter ?mode=):
 * - agenda  : aktivitas SATU pegawai, dikelompokkan per tanggal (default)
 * - matriks : beban banyak pegawai x 14 hari (hanya kalau boleh melihat > 1 pegawai)
 *
 * Rentang default mode agenda: 30 hari ke belakang sampai 30 hari ke depan.
 *
 * Isi mode agenda:
 * - Telat      : disposisi terbuka yang batas waktunya lewat (bukan kegiatan berjadwal yang sudah lewat)
 * - Hari ini + mendatang : urut tanggal naik
 * - Riwayat    : kegiatan yang tanggalnya sudah lewat (urut tanggal turun), termasuk yang sudah selesai
 * - Belum terjadwal : terbuka, tanpa tanggal kegiatan dan tanpa batas waktu
 *
 * Hak lihat:
 * - admin / super admin     : semua pegawai
 * - TU Unit & Eselon II     : pegawai di unitnya
 * - lainnya                 : dirinya sendiri
 *
 * Tanggal di jadwal memakai aturan yang sama dengan kalender:
 * tanggal kegiatan surat -> batas waktu -> tanggal disposisi dikirim.
 */
class JadwalPegawaiController extends Controller
{
    private const TGL = 'DATE(COALESCE(disposisi_surat.tanggal_kegiatan, disposisi.batas_waktu, disposisi.created_at))';

    public const JENIS = [
        'surat_masuk' => 'Surat Masuk',
        'undangan' => 'Undangan Masuk',
        'nota_dinas' => 'Nota Dinas',
        'nota_pimpinan' => 'Nota Dinas ke Pimpinan',
    ];

    private const HARI_MATRIKS = 14;
    private const MAKS_PEGAWAI_MATRIKS = 80;
    private const HARI_MUNDUR = 30;
    private const HARI_MAJU = 30;

    private ?bool $adaJenis = null;

    /** Link lama (/kalender/jadwal) diarahkan ke tab di halaman Disposisi. */
    public function index(Request $request)
    {
        return redirect()->route('disposisi.index', ['tampil' => 'jadwal'] + $request->query());
    }

    /** Data tab Jadwal Pegawai (dipanggil DisposisiController@index). */
    public function data(Request $request): array
    {
        $user = $request->user();
        $hariIni = now()->toDateString();

        $daftar = $this->pegawaiBoleh($user);
        $pegawai = $daftar->firstWhere('id', (int) $request->query('pegawai', $user->id));
        abort_unless($pegawai, 403, 'Anda tidak boleh melihat jadwal pegawai ini.');

        $f = $this->filter($request);
        $mode = ($request->query('mode') === 'matriks' && $daftar->count() > 1) ? 'matriks' : 'agenda';

        // Matriks mulai dari hari ini; agenda mulai 30 hari ke belakang supaya riwayat ikut tampil
        $dari = $this->tgl($request->query('dari'))
            ?? ($mode === 'matriks' ? $hariIni : Carbon::parse($hariIni)->subDays(self::HARI_MUNDUR)->toDateString());

        $sampaiDefault = Carbon::parse($hariIni)->addDays(self::HARI_MAJU)->toDateString();
        if ($sampaiDefault < $dari) {
            $sampaiDefault = Carbon::parse($dari)->addDays(29)->toDateString();
        }
        $sampai = $this->tgl($request->query('sampai')) ?? $sampaiDefault;
        if ($sampai < $dari) {
            $sampai = $dari;
        }
        $batas = Carbon::parse($dari)->addDays(120)->toDateString();
        if ($sampai > $batas) {
            $sampai = $batas;
        }

        $data = $mode === 'matriks'
            ? $this->dataMatriks($daftar, $dari, $f, $hariIni)
            : $this->dataAgenda($pegawai, $dari, $sampai, $f, $hariIni);

        return [
            'tampil' => 'jadwal',
            'mode' => $mode,
            'daftar' => $daftar,
            'pegawai' => $pegawai,
            'f' => $f,
            'dari' => $dari,
            'sampai' => $sampai,
            'hariIni' => $hariIni,
            'jenisList' => self::JENIS,
            'adaJenis' => $this->adaJenis(),
        ] + $data;
    }

    // ================================================================ data

    private function dataAgenda(User $pegawai, string $dari, string $sampai, array $f, string $hariIni): array
    {
        $dasar = $this->dasar([$pegawai->id], $f);
        // 'surat.disposisi.kepada' sengaja tidak di-eager-load: 'ditandai' tidak dipakai kartu tab ini
        // (hemat 2 query + ratusan model per render)
        $with = ['surat.unit', 'dari:id,name'];
        $kolom = ['disposisi.*', DB::raw(self::TGL . ' as tgl_kal')];

        // Telat: masih terbuka, batas waktu lewat, dan BUKAN kegiatan berjadwal yang sudah lewat
        // (kegiatan yang sudah lewat masuk Riwayat)
        $telat = (clone $dasar)->select($kolom)
            ->whereIn('disposisi.status', Disposisi::TERBUKA)
            ->whereNotNull('disposisi.batas_waktu')
            ->whereDate('disposisi.batas_waktu', '<', $hariIni)
            ->where(fn($w) => $w->whereNull('disposisi_surat.tanggal_kegiatan')
                ->orWhereDate('disposisi_surat.tanggal_kegiatan', '>=', $hariIni))
            ->with($with)->orderBy('disposisi.batas_waktu')->get();

        // Belum terjadwal: terbuka, tanpa tanggal kegiatan dan tanpa batas waktu
        $belum = (clone $dasar)->select($kolom)
            ->whereIn('disposisi.status', Disposisi::TERBUKA)
            ->whereNull('disposisi_surat.tanggal_kegiatan')
            ->whereNull('disposisi.batas_waktu')
            ->with($with)->orderByDesc('disposisi.id')->get();

        $sudah = $telat->pluck('id')->merge($belum->pluck('id'))->flip();

        $agenda = (clone $dasar)->select($kolom)
            ->whereRaw(self::TGL . ' >= ?', [$dari])
            ->whereRaw(self::TGL . ' <= ?', [$sampai])
            ->with($with)->get()
            ->reject(fn($d) => $sudah->has($d->id))
            ->sortBy([
                fn($a, $b) => (string) $a->tgl_kal <=> (string) $b->tgl_kal,
                fn($a, $b) => ($a->surat?->waktu_mulai ?? '99') <=> ($b->surat?->waktu_mulai ?? '99'),
                fn($a, $b) => $a->id <=> $b->id,
            ])->values();

        $tglD = fn($d) => Carbon::parse($d->tgl_kal)->toDateString();

        $perHari = $agenda
            ->groupBy($tglD)
            ->map(fn($grup, $tgl) => [
                'tanggal' => $tgl,
                'lewat' => $tgl < $hariIni,
                'kartu' => $this->tandaiBentrok($grup->map(fn($d) => $this->kartu($d, $tgl))->values()->all()),
            ])->values();

        // Hari ini + mendatang: tanggal naik. Riwayat: tanggal turun (terbaru di atas).
        $hari = $perHari->where('lewat', false)->values();
        $riwayat = $perHari->where('lewat', true)->sortByDesc('tanggal')->values();

        return [
            'ringkasan' => [
                'hari_ini' => $agenda->filter(fn($d) => $tglD($d) === $hariIni)->count(),
                'mendatang' => $agenda->filter(fn($d) => in_array($d->status, Disposisi::TERBUKA, true) && $tglD($d) > $hariIni)->count(),
                'belum' => $belum->count(),
                'telat' => $telat->count(),
                'selesai' => $agenda->where('status', 'selesai')->count(),
                'riwayat' => $agenda->filter(fn($d) => $tglD($d) < $hariIni)->count(),
            ],
            'telat' => $telat->map(fn($d) => $this->kartu($d, $tglD($d)))->values()->all(),
            'belum' => $belum->map(fn($d) => $this->kartu($d, $tglD($d)))->values()->all(),
            'hari' => $hari,
            'riwayat' => $riwayat,
        ];
    }

    private function dataMatriks(Collection $daftar, string $dari, array $f, string $hariIni): array
    {
        $ids = $daftar->pluck('id')->take(self::MAKS_PEGAWAI_MATRIKS)->all();
        $hariKolom = collect(range(0, self::HARI_MATRIKS - 1))->map(fn($i) => Carbon::parse($dari)->addDays($i));
        $akhir = $hariKolom->last()->toDateString();

        $baris = $this->dasar($ids, $f)
            ->select('disposisi.kepada_user_id', 'disposisi.status', 'disposisi.batas_waktu', DB::raw(self::TGL . ' as tgl_kal'))
            ->whereRaw(self::TGL . ' >= ?', [$dari])
            ->whereRaw(self::TGL . ' <= ?', [$akhir])
            ->get();

        $sel = [];
        foreach ($baris as $r) {
            $k = Carbon::parse($r->tgl_kal)->toDateString();
            $c = $sel[$r->kepada_user_id][$k] ?? ['n' => 0, 'terbuka' => 0, 'telat' => false];
            $c['n']++;
            if (in_array($r->status, Disposisi::TERBUKA, true)) {
                $c['terbuka']++;
                if ($r->batas_waktu && $r->batas_waktu->lt(now()->startOfDay())) {
                    $c['telat'] = true;
                }
            }
            $sel[$r->kepada_user_id][$k] = $c;
        }

        $aktif = Disposisi::whereIn('kepada_user_id', $ids)->whereIn('status', Disposisi::TERBUKA)
            ->selectRaw('kepada_user_id, COUNT(*) as n')->groupBy('kepada_user_id')->pluck('n', 'kepada_user_id');

        $telat = Disposisi::whereIn('kepada_user_id', $ids)->whereIn('status', Disposisi::TERBUKA)
            ->whereNotNull('batas_waktu')->whereDate('batas_waktu', '<', $hariIni)
            ->selectRaw('kepada_user_id, COUNT(*) as n')->groupBy('kepada_user_id')->pluck('n', 'kepada_user_id');

        return [
            'hariKolom' => $hariKolom,
            'sel' => $sel,
            'aktif' => $aktif,
            'telatTotal' => $telat,
            'baris' => $daftar->take(self::MAKS_PEGAWAI_MATRIKS),
            'terpotong' => $daftar->count() > self::MAKS_PEGAWAI_MATRIKS,
        ];
    }

    // ================================================================ helper

    /** Pegawai yang boleh dilihat jadwalnya (selalu termasuk diri sendiri). */
    private function pegawaiBoleh(User $user): Collection
    {
        $q = User::query()->with('unit:id,kode,nama')->select('users.*')->orderBy('name');

        if ($user->isAdmin()) {
            // semua pegawai
        } elseif (in_array($user->role, [User::ROLE_TU_UNIT, User::ROLE_ESELON2], true) && $user->unit_id) {
            $q->where('unit_id', $user->unit_id);
        } else {
            $q->whereKey($user->id);
        }

        return $q->get();
    }

    /** Query dasar: disposisi diterima para pegawai + filter. Belum ada select. */
    private function dasar(array $userIds, array $f)
    {
        $q = Disposisi::query()
            ->join('disposisi_surat', 'disposisi_surat.id', '=', 'disposisi.surat_id')
            ->whereIn('disposisi.kepada_user_id', $userIds ?: [0]);

        if ($f['status'] === 'perlu') {
            $q->whereIn('disposisi.status', Disposisi::TERBUKA);
        } elseif ($f['status'] !== '') {
            $q->where('disposisi.status', $f['status']);
        }
        if ($f['sifat'] !== '') {
            $q->where('disposisi_surat.sifat', $f['sifat']);
        }
        if ($f['jenis'] !== '' && $this->adaJenis()) {
            $q->where('disposisi_surat.jenis', $f['jenis']);
        }
        if ($f['q'] !== '') {
            $kata = $f['q'];
            $q->where(fn($w) => $w->where('disposisi_surat.perihal', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.nomor_surat', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.asal_surat', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.tempat', 'like', "%{$kata}%"));
        }

        return $q;
    }

    /** Satu disposisi -> data kartu untuk view. */
    private function kartu(Disposisi $d, string $tgl): array
    {
        $s = $d->surat;
        $tempat = trim((string) $s?->tempat);

        // Link daring: kolom link_daring, atau link yang terselip di kolom tempat
        $link = null;
        if (!empty($s?->link_daring) && preg_match('#^https?://#i', $s->link_daring)) {
            $link = $s->link_daring;
        }
        $pola = '#https?://[^\s<>"\']+#i';
        if (!$link && preg_match($pola, $tempat, $m)) {
            $link = $m[0];
            $tempat = rtrim(trim(preg_replace($pola, '', $tempat)), " :-–");
        }

        $berjam = $s?->tanggal_kegiatan && $s->waktu_mulai;
        $mulai = $berjam ? $this->menit($s->waktu_mulai) : null;
        $selesaiMnt = $berjam ? $this->menit($s->waktu_selesai) : null;
        $akhir = $mulai !== null ? (($selesaiMnt !== null && $selesaiMnt > $mulai) ? $selesaiMnt : $mulai + 60) : null;

        // Hanya dihitung kalau relasinya memang sudah dimuat (tidak memicu query tambahan)
        $ditandai = ($s && $s->relationLoaded('disposisi'))
            ? $s->disposisi->map(fn($x) => $x->relationLoaded('kepada') ? $x->kepada?->name : null)->filter()->unique()->values()->all()
            : [];

        $jenis = $s?->jenis ?: 'surat_masuk';

        return [
            'id' => $d->id,
            'surat_id' => $d->surat_id,
            'perihal' => $s?->perihal ?? 'Surat',
            'nomor_surat' => $s?->nomor_surat,
            'asal' => $s?->asal_surat,
            'jenis' => $jenis,
            'jenis_label' => self::JENIS[$jenis] ?? ucfirst(str_replace('_', ' ', $jenis)),
            'sifat' => $s?->sifat,
            'sifat_label' => $s?->sifat_label,
            'unit' => $s?->unit?->kode ?? $s?->unit?->nama,
            'status' => $d->status,
            'status_label' => $d->status_label,
            'terlambat' => $d->terlambat,
            'tanggal' => $tgl,
            'waktu' => $berjam ? ($s->waktu_label ?? null) : ($s?->tanggal_kegiatan ? 'Seharian' : null),
            'batas' => $d->batas_waktu,
            'tempat' => $tempat ?: null,
            'link' => $link,
            'ditandai' => $ditandai,
            'instruksi' => $d->instruksi_label,
            'dari' => $d->dari?->name,
            'mulai' => $mulai,
            'akhir' => $akhir,
            'bentrok' => false,
            // Kapan pegawai ini menandai selesai (untuk log aktivitas)
            'selesai_at' => $d->selesai_at ? Carbon::parse($d->selesai_at)->translatedFormat('d M Y H:i') : null,
            'url' => route('disposisi.index', ['tampil' => 'kalender', 'tanggal' => $tgl, 'buka' => $d->surat_id]),
        ];
    }

    /** Tandai kegiatan berjam yang jamnya tabrakan (selain yang sudah selesai / satu surat yang sama). */
    private function tandaiBentrok(array $kartu): array
    {
        foreach ($kartu as $i => $a) {
            if ($a['mulai'] === null || $a['status'] === 'selesai') {
                continue;
            }
            foreach ($kartu as $j => $b) {
                if ($i === $j || $b['mulai'] === null || $b['status'] === 'selesai' || $a['surat_id'] === $b['surat_id']) {
                    continue;
                }
                if ($a['mulai'] < $b['akhir'] && $b['mulai'] < $a['akhir']) {
                    $kartu[$i]['bentrok'] = true;
                    break;
                }
            }
        }

        return $kartu;
    }

    private function menit($t): ?int
    {
        if (!$t) {
            return null;
        }
        $p = explode(':', substr((string) $t, 0, 5));

        return ((int) ($p[0] ?? 0)) * 60 + (int) ($p[1] ?? 0);
    }

    private function filter(Request $r): array
    {
        $status = (string) $r->query('status', '');
        $sifat = (string) $r->query('sifat', '');
        $jenis = (string) $r->query('jenis', '');

        return [
            'status' => $status === 'perlu' || array_key_exists($status, Disposisi::STATUS) ? $status : '',
            'sifat' => array_key_exists($sifat, DisposisiSurat::SIFAT) ? $sifat : '',
            'jenis' => array_key_exists($jenis, self::JENIS) ? $jenis : '',
            'q' => Str::limit(trim((string) $r->query('q', '')), 100, ''),
        ];
    }

    private function tgl($v): ?string
    {
        return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
    }

    /** Aman dipakai sebelum migrasi jenis dijalankan. */
    private function adaJenis(): bool
    {
        return $this->adaJenis ??= Schema::hasColumn('disposisi_surat', 'jenis');
    }
}