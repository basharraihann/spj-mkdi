<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\DisposisiSurat;
use App\Models\GoogleKalenderAkun;
use App\Services\GoogleKalenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Pendukung tab Hari Ini dan Kalender di halaman Disposisi, untuk SEMUA akun.
 * - Data tab Hari Ini dan tab Kalender (dipanggil DisposisiController@index)
 * - Sumber event FullCalendar dan detail popup (JSON)
 * - Hubungkan / sinkronkan akun Google Calendar
 *
 * Yang tampil: disposisi yang DITERIMA user dan yang DIKIRIM user.
 * Tanggal di kalender = tanggal kegiatan surat -> batas waktu -> tanggal disposisi dikirim.
 */
class KalenderDisposisiController extends Controller
{
    /** Ekspresi SQL tanggal kalender (MySQL & SQLite sama-sama punya COALESCE dan DATE). */
    private const TGL = 'DATE(COALESCE(disposisi_surat.tanggal_kegiatan, disposisi.batas_waktu, disposisi.created_at))';

    /** Link lama (/kalender) diarahkan ke tab di halaman Disposisi. */
    public function index(Request $request)
    {
        $tampil = $request->query('tab') === 'kalender' ? 'kalender' : 'hari-ini';

        return redirect()->route('disposisi.index', ['tampil' => $tampil] + $request->except(['tab']));
    }

    /** Data untuk tab Hari Ini di halaman Disposisi (dipanggil DisposisiController@index). */
    public function dataHariIni(Request $request): array
    {
        $user = $request->user();
        $hariIni = now()->toDateString();
        $f = $this->filter($request);
        $hari = $this->tgl($request->query('hari')) ?? $hariIni;

        return [
            'f' => $f,
            'hari' => $hari,
            'daftarHari' => $this->daftarHari($user->id, $hari, $hari === $hariIni, $f),
            'bukaSurat' => (int) $request->query('buka') ?: null,
            'google' => [
                'aktif' => GoogleKalenderService::aktif(),
                'akun' => GoogleKalenderAkun::where('user_id', $user->id)->first(),
            ],
            'ringkasan' => [
                'baru' => Disposisi::where('kepada_user_id', $user->id)->where('status', 'baru')->count(),
            ],
        ];
    }

    /** Data untuk tab Kalender di halaman Disposisi (dipanggil DisposisiController@index). */
    public function dataKalender(Request $request): array
    {
        $user = $request->user();
        $f = $this->filter($request);

        $view = in_array($request->query('view'), ['dayGridMonth', 'timeGridWeek', 'timeGridDay', 'multiMonthYear', 'listWeek'], true)
            ? $request->query('view') : null;

        return [
            'f' => $f,
            'tanggal' => $this->tgl($request->query('tanggal')) ?? now()->toDateString(),
            'view' => $view,
            'rapatHariIni' => $this->rapatHariIni($user->id),
            'bukaSurat' => (int) $request->query('buka') ?: null,
        ];
    }

    /** Sumber event FullCalendar: ?start=...&end=... (+ filter yang sama dengan halaman) */
    public function events(Request $request)
    {
        $user = $request->user();
        $f = $this->filter($request);
        $start = Carbon::parse($request->query('start', now()->startOfMonth()))->toDateString();
        $end = Carbon::parse($request->query('end', now()->endOfMonth()))->toDateString();

        $q = Disposisi::query()
            ->select('disposisi.*', DB::raw(self::TGL . ' as tgl_kal'))
            ->join('disposisi_surat', 'disposisi_surat.id', '=', 'disposisi.surat_id')
            ->whereRaw(self::TGL . ' >= ?', [$start])
            ->whereRaw(self::TGL . ' < ?', [$end])
            ->with(['surat.disposisi.kepada', 'dari', 'kepada']);
        $this->terapkan($q, $f);

        // 1. Diterima saya: satu event per disposisi (avatar = penerima disposisi terakhir)
        $masuk = $f['arah'] === 'keluar' ? collect() : (clone $q)->where('disposisi.kepada_user_id', $user->id)->get()
            ->map(fn($d) => $this->event(
                $d,
                'masuk',
                $d->status,
                ($d->status === 'selesai' ? '✓ ' : '') . Str::limit($d->surat?->perihal ?? 'Surat', 60),
                'Dari ' . ($d->dari?->name ?? '-'),
                $this->penerimaAkhir($d->surat)
            ));

        // 2. Dikirim saya: satu event per surat (penerima digabung, avatar = semua penerima)
        $keluar = $f['arah'] === 'masuk' ? collect() : (clone $q)->where('disposisi.dari_user_id', $user->id)->get()
            ->groupBy('surat_id')
            ->map(function ($grup) {
                $d = $grup->first();
                $selesai = $grup->every(fn($x) => $x->status === 'selesai');
                $nama = $grup->map(fn($x) => $x->kepada?->name)->filter()->unique()->values();

                return $this->event(
                    $d,
                    'keluar',
                    $selesai ? 'selesai' : 'keluar',
                    '↗ ' . Str::limit($d->surat?->perihal ?? 'Surat', 55),
                    'Ke ' . $nama->take(3)->implode(', ') . ($nama->count() > 3 ? ' +' . ($nama->count() - 3) : ''),
                    $this->penerimaAkhir($d->surat)
                );
            })
            ->values();

        return response()->json($masuk->concat($keluar)->values());
    }

    /** Detail untuk popup kalender (JSON). */
    public function detail(Request $request, DisposisiSurat $surat)
    {
        $user = $request->user();
        abort_unless($surat->bolehDilihat($user), 403);

        $surat->load(['disposisi.dari.unit', 'disposisi.kepada.unit', 'feedback.user.unit']);
        $tgl = fn($d) => $d ? Carbon::parse($d)->translatedFormat('d M Y') : null;

        $milik = $surat->disposisi->filter(fn($d) => (int) $d->kepada_user_id === (int) $user->id);

        return response()->json([
            'id' => $surat->id,
            'nomor_surat' => $surat->nomor_surat,
            'perihal' => $surat->perihal,
            'asal_surat' => $surat->asal_surat,
            'sifat' => $surat->sifat_label,
            'status' => $surat->status,
            'tanggal_kegiatan' => $tgl($surat->tanggal_kegiatan),
            'waktu' => $surat->waktu_label,
            'tempat' => $surat->tempat,
            'url_detail' => route('disposisi.show', $surat),
            'url_feedback' => route('disposisi.feedback', $surat),
            'url_berkas' => $surat->link_berkas ?: ($surat->file_path ? route('disposisi.berkas', $surat) : null),
            'disposisi' => $surat->disposisi->map(fn($d) => [
                'dari' => $d->dari?->name,
                'kepada' => $d->kepada?->name,
                'kepada_unit' => $d->kepada?->unit?->nama,
                'instruksi' => $d->instruksi_label,
                'catatan' => $d->catatan,
                'batas' => $tgl($d->batas_waktu),
                'terlambat' => $d->terlambat,
                'status' => $d->status,
                'status_label' => $d->status_label,
                'saya' => (int) $d->kepada_user_id === (int) $user->id,
            ])->values(),
            'feedback' => $surat->feedback->map(fn($f) => [
                'nama' => $f->user?->name ?? '-',
                'foto' => $this->orang($f->user)['foto'] ?? null,
                'unit' => $f->user?->unit?->nama,
                'isi' => $f->isi,
                'waktu' => $f->created_at?->translatedFormat('d M Y H:i'),
                'saya' => (int) $f->user_id === (int) $user->id,
            ])->values(),
            // Checkbox "tandai selesai" hanya untuk penerima yang disposisinya belum selesai
            'bisa_selesai' => $milik->contains(fn($d) => $d->status !== 'selesai'),
        ]);
    }

    // ================================================================ Google Calendar

    private function kembali(): string
    {
        return route('disposisi.index', ['tampil' => 'hari-ini']);
    }

    public function googleHubungkan(Request $request)
    {
        if (!GoogleKalenderService::aktif()) {
            return redirect($this->kembali())
                ->with('error', 'Integrasi Google Calendar belum diatur admin (GOOGLE_CLIENT_ID / SECRET di .env).');
        }

        $state = Str::random(40);
        $request->session()->put('google_kalender_state', $state);

        return redirect()->away(GoogleKalenderService::urlIzin($state));
    }

    public function googleCallback(Request $request)
    {
        $state = $request->session()->pull('google_kalender_state');

        if ($request->filled('error')) {
            return redirect($this->kembali())->with('error', 'Izin Google Calendar dibatalkan.');
        }
        if (!$state || !hash_equals($state, (string) $request->query('state')) || !$request->filled('code')) {
            return redirect($this->kembali())->with('error', 'Sesi menghubungkan Google kedaluwarsa. Coba lagi.');
        }

        try {
            $akun = GoogleKalenderService::simpanDariKode($request->user(), (string) $request->query('code'));
        } catch (\Throwable $e) {
            report($e);

            return redirect($this->kembali())->with('error', 'Gagal menghubungkan Google Calendar: ' . $e->getMessage());
        }

        $n = GoogleKalenderService::sinkronSemuaMilik($request->user());

        return redirect($this->kembali())->with(
            'success',
            'Google Calendar terhubung' . ($akun->google_email ? " ({$akun->google_email})" : '') . '.'
            . ($n ? " {$n} disposisi aktif sudah masuk ke kalender Google Anda." : '')
        );
    }

    public function googleSinkron(Request $request)
    {
        $n = GoogleKalenderService::sinkronSemuaMilik($request->user());
        $err = GoogleKalenderAkun::where('user_id', $request->user()->id)->value('error_terakhir');

        return redirect($this->kembali())->with(
            $err ? 'error' : 'success',
            $err ?: "{$n} disposisi aktif disinkronkan ke Google Calendar."
        );
    }

    public function googlePutuskan(Request $request)
    {
        $akun = GoogleKalenderAkun::where('user_id', $request->user()->id)->first();
        if ($akun) {
            GoogleKalenderService::putuskan($akun);
        }

        return redirect($this->kembali())->with('success', 'Google Calendar diputuskan. Event yang sudah ada di Google tidak dihapus.');
    }

    // ================================================================ helper

    /** Filter dari query string, sudah dibersihkan. */
    private function filter(Request $r): array
    {
        $status = (string) $r->query('status', '');
        $sifat = (string) $r->query('sifat', '');

        return [
            'arah' => in_array($r->query('arah'), ['masuk', 'keluar'], true) ? $r->query('arah') : 'semua',
            'status' => $status === 'perlu' || array_key_exists($status, Disposisi::STATUS) ? $status : '',
            'sifat' => array_key_exists($sifat, DisposisiSurat::SIFAT) ? $sifat : '',
            'q' => Str::limit(trim((string) $r->query('q', '')), 100, ''),
        ];
    }

    /** Terapkan filter status, sifat, kata kunci ke query yang sudah join disposisi_surat. */
    private function terapkan($q, array $f): void
    {
        if ($f['status'] === 'perlu') {
            $q->whereIn('disposisi.status', Disposisi::TERBUKA);
        } elseif ($f['status'] !== '') {
            $q->where('disposisi.status', $f['status']);
        }
        if ($f['sifat'] !== '') {
            $q->where('disposisi_surat.sifat', $f['sifat']);
        }
        if ($f['q'] !== '') {
            $kata = $f['q'];
            $q->where(fn($w) => $w->where('disposisi_surat.perihal', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.nomor_surat', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.asal_surat', 'like', "%{$kata}%")
                ->orWhere('disposisi_surat.tempat', 'like', "%{$kata}%"));
        }
    }

    private function tgl($v): ?string
    {
        return is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && strtotime($v) ? $v : null;
    }

    /**
     * Data satu orang untuk avatar: nama + URL foto (kalau ada).
     * SESUAIKAN baris $foto di bawah dengan nama kolom foto di tabel users kamu.
     */
    private function orang($u): ?array
    {
        if (!$u) {
            return null;
        }

        $foto = $u->profile_photo_url ?? $u->avatar_url ?? $u->avatar ?? $u->foto ?? null;
        if ($foto && !preg_match('#^(https?:)?//|^/#', $foto)) {
            $foto = asset('storage/' . ltrim($foto, '/'));
        }

        return ['nama' => $u->name, 'foto' => $foto ?: null];
    }

    /** Kumpulan user -> array avatar (tanpa duplikat). */
    private function daftarOrang($users): array
    {
        return collect($users)->filter()->unique('id')->values()
            ->map(fn($u) => $this->orang($u))->all();
    }

    /**
     * Penerima disposisi TERAKHIR pada satu surat = orang yang menerima disposisi
     * tetapi tidak meneruskannya lagi ke orang lain (ujung rantai disposisi).
     * Contoh: Pimpinan -> Achmad -> (Albi, Bashar, Tria)  =>  Albi, Bashar, Tria.
     */
    private function penerimaAkhir($surat): array
    {
        $semua = $surat?->disposisi ?? collect();
        $peneruslah = $semua->pluck('dari_user_id')->filter()->unique();

        $akhir = $semua->reject(fn($x) => $peneruslah->contains($x->kepada_user_id));
        if ($akhir->isEmpty()) {
            $akhir = $semua;   // jaga-jaga kalau rantainya aneh
        }

        return $this->daftarOrang($akhir->map(fn($x) => $x->kepada));
    }

    /** Rapat / kegiatan yang dijadwalkan HARI INI (masuk + keluar), urut jam. */
    private function rapatHariIni(int $userId)
    {
        $tanpaFilter = ['arah' => 'semua', 'status' => '', 'sifat' => '', 'q' => ''];

        return $this->daftarHari($userId, now()->toDateString(), false, $tanpaFilter)
            ->sortBy(fn($r) => (string) ($r['surat']?->waktu_mulai ?? '99'))
            ->values();
    }

    /**
     * Isi tab "Disposisi Hari Ini" untuk satu tanggal.
     * Masuk: yang dijadwalkan pada tanggal itu (+ semua yang belum dibaca kalau tanggalnya hari ini).
     * Keluar: surat yang saya disposisikan dan dijadwalkan pada tanggal itu.
     */
    private function daftarHari(int $userId, string $hari, bool $hariIni, array $f)
    {
        $hasil = collect();

        if ($f['arah'] !== 'keluar') {
            $q = $this->milikSaya($userId)->with(['surat.disposisi.kepada', 'dari.unit']);
            $q->where(function ($w) use ($hari, $hariIni, $f) {
                $w->whereRaw(self::TGL . ' = ?', [$hari]);
                if ($hariIni && $f['status'] === '') {
                    $w->orWhere('disposisi.status', 'baru');
                }
            });
            $this->terapkan($q, $f);

            foreach ($q->get() as $d) {
                $hasil->push([
                    'arah' => 'masuk',
                    'surat_id' => $d->surat_id,
                    'surat' => $d->surat,
                    'orang' => $d->dari?->name ?? '-',
                    'orang_unit' => $d->dari?->unit?->nama,
                    'orang_users' => $this->penerimaAkhir($d->surat),
                    'instruksi' => $d->instruksi_label,
                    'status' => $d->status,
                    'status_label' => $d->status_label,
                    'terlambat' => $d->terlambat,
                    'batas' => $d->batas_waktu,
                    'dijadwalkan' => Carbon::parse($d->tanggal_kalender)->toDateString() === $hari,
                ]);
            }
        }

        if ($f['arah'] !== 'masuk') {
            $q = Disposisi::query()->select('disposisi.*')
                ->join('disposisi_surat', 'disposisi_surat.id', '=', 'disposisi.surat_id')
                ->where('disposisi.dari_user_id', $userId)
                ->whereRaw(self::TGL . ' = ?', [$hari])
                ->with(['surat.disposisi.kepada', 'kepada']);
            $this->terapkan($q, $f);

            foreach ($q->get()->groupBy('surat_id') as $grup) {
                $d = $grup->first();
                $selesai = $grup->every(fn($x) => $x->status === 'selesai');
                $nama = $grup->map(fn($x) => $x->kepada?->name)->filter()->unique()->values();
                $hasil->push([
                    'arah' => 'keluar',
                    'surat_id' => $d->surat_id,
                    'surat' => $d->surat,
                    'orang' => $nama->take(2)->implode(', ') . ($nama->count() > 2 ? ' +' . ($nama->count() - 2) : ''),
                    'orang_unit' => $nama->count() . ' penerima',
                    'orang_users' => $this->penerimaAkhir($d->surat),
                    'instruksi' => $d->instruksi_label,
                    'status' => $selesai ? 'selesai' : 'keluar',
                    'status_label' => $selesai ? 'Selesai' : $grup->where('status', 'selesai')->count() . '/' . $grup->count() . ' selesai',
                    'terlambat' => false,
                    'batas' => $d->batas_waktu,
                    'dijadwalkan' => true,
                ]);
            }
        }

        // Belum dibaca dulu, lalu urut jam kegiatan (tanpa jam di akhir)
        return $hasil->sortBy([
            fn($a, $b) => ($a['status'] === 'baru' ? 0 : 1) <=> ($b['status'] === 'baru' ? 0 : 1),
            fn($a, $b) => ($a['surat']?->waktu_mulai ?? '99') <=> ($b['surat']?->waktu_mulai ?? '99'),
        ])->values();
    }

    private function milikSaya(int $userId)
    {
        return Disposisi::query()
            ->select('disposisi.*')
            ->join('disposisi_surat', 'disposisi_surat.id', '=', 'disposisi.surat_id')
            ->where('disposisi.kepada_user_id', $userId);
    }

    private function event(Disposisi $d, string $arah, string $kelas, string $judul, string $sub, array $orang = []): array
    {
        $s = $d->surat;
        $tgl = Carbon::parse($d->tgl_kal)->toDateString();
        $ev = [
            'id' => $arah . '-' . $d->id,
            'title' => $judul,
            // Warna diatur lewat CSS di halaman (ev-baru, ev-dibaca, ev-diteruskan, ev-selesai, ev-keluar)
            'classNames' => ['ev', 'ev-' . $kelas],
            'extendedProps' => [
                'surat_id' => $d->surat_id,
                'arah' => $arah,
                'sub' => $sub,
                'tempat' => $s?->tempat,
                'sifat' => $s?->sifat,
                'orang' => $orang,   // [{nama, foto}] -> dipakai untuk avatar di event
            ],
        ];

        if ($s?->tanggal_kegiatan && $s->waktu_mulai) {
            $ev['start'] = $tgl . 'T' . substr((string) $s->waktu_mulai, 0, 5);
            if ($s->waktu_selesai) {
                $ev['end'] = $tgl . 'T' . substr((string) $s->waktu_selesai, 0, 5);
            }
        } else {
            $ev['start'] = $tgl;
            $ev['allDay'] = true;
        }

        return $ev;
    }
}