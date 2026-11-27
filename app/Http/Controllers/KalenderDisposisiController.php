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
 * Pendukung tab Kalender di halaman Disposisi, untuk SEMUA akun.
 * - Data tab Kalender (dipanggil DisposisiController@index)
 * - Sumber event FullCalendar dan detail popup (JSON)
 * - Hubungkan / sinkronkan akun Google Calendar
 *
 * Yang tampil: semua surat BERJADWAL (satu event per surat), ditambah surat tanpa jadwal
 * yang melibatkan user (sebagai penerima atau pengirim).
 * Tanggal di kalender = tanggal kegiatan surat -> batas waktu terdekat -> tanggal disposisi pertama -> tanggal surat dibuat.
 *
 * Warna kartu (4 saja):
 * - baru   : disposisi untuk saya yang belum dibuka
 * - dibaca : sudah dibaca / diteruskan / dikirim oleh saya
 * - selesai: disposisi saya selesai
 * - umum   : agenda yang bukan disposisi ke saya
 */
class KalenderDisposisiController extends Controller
{
    /** Ekspresi SQL tanggal kalender per surat (MySQL & SQLite sama-sama punya COALESCE dan DATE). */
    private const TGL = 'DATE(COALESCE(disposisi_surat.tanggal_kegiatan,
        (SELECT MIN(dx.batas_waktu) FROM disposisi dx WHERE dx.surat_id = disposisi_surat.id),
        (SELECT MIN(dy.created_at) FROM disposisi dy WHERE dy.surat_id = disposisi_surat.id),
        disposisi_surat.created_at))';

    /** Urutan prioritas status disposisi milik user (yang butuh tindakan dulu). */
    private const URUT = ['baru' => 0, 'dibaca' => 1, 'diteruskan' => 2, 'selesai' => 3];

    /** Link lama (/kalender) diarahkan ke tab di halaman Disposisi. */
    public function index(Request $request)
    {
        return redirect()->route('disposisi.index', ['tampil' => 'kalender'] + $request->except(['tab', 'hari']));
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
            'rapatHariIni' => $this->rapatHariIni($user),
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

        $q = DisposisiSurat::query()
            ->select('disposisi_surat.*', DB::raw(self::TGL . ' as tgl_kal'))
            ->whereRaw(self::TGL . ' >= ?', [$start])
            ->whereRaw(self::TGL . ' < ?', [$end])
            ->with(['disposisi.kepada', 'disposisi.dari']);
        $this->terapkan($q, $f);

        $hasil = collect();
        foreach ($q->get() as $s) {
            $r = $this->ringkas($s, $user);

            // Surat tanpa jadwal kegiatan hanya tampil untuk yang terlibat
            if (!$s->tanggal_kegiatan && !$r['saya_masuk'] && !$r['saya_kirim']) {
                continue;
            }

            // Filter arah dan status dihitung dari posisi user pada surat ini
            if ($f['arah'] === 'masuk' && !$r['saya_masuk']) {
                continue;
            }
            if ($f['arah'] === 'keluar' && !$r['saya_kirim']) {
                continue;
            }
            if ($f['status'] === 'perlu' && !$r['perlu']) {
                continue;
            }
            if ($f['status'] !== '' && $f['status'] !== 'perlu' && $r['status'] !== $f['status']) {
                continue;
            }

            $perihal = $s->perihal ?? 'Surat';
            $judul = match ($r['arah']) {
                'keluar' => '↗ ' . Str::limit($perihal, 55),
                default => ($r['status'] === 'selesai' ? '✓ ' : '') . Str::limit($perihal, 60),
            };

            $hasil->push($this->event($s, $r, $judul));
        }

        return response()->json($hasil->values());
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
                'ikut' => (bool) $d->ikut,
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
            'link_daring' => $surat->link_daring,
        ]);
    }

    // ================================================================ Google Calendar

    private function kembali(): string
    {
        return route('disposisi.index', ['tampil' => 'kalender']);
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

    /** Terapkan filter sifat dan kata kunci ke query DisposisiSurat. (Arah & status dihitung di PHP lewat ringkas()) */
    private function terapkan($q, array $f): void
    {
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
     * Data satu orang untuk avatar: id, nama + URL foto (kalau ada).
     * SESUAIKAN baris $foto di bawah dengan nama kolom/accessor foto di model User kamu.
     */
    private function orang($u): ?array
    {
        if (!$u) {
            return null;
        }

        $foto = $u->foto_url ?? $u->profile_photo_url ?? $u->avatar_url ?? $u->avatar ?? $u->foto ?? null;
        if ($foto && !preg_match('#^(https?:)?//|^/#', $foto)) {
            $foto = asset('storage/' . ltrim($foto, '/'));
        }

        return ['id' => $u->id, 'nama' => $u->name, 'foto' => $foto ?: null];
    }

    /** Kumpulan user -> array avatar (tanpa duplikat). */
    private function daftarOrang($users): array
    {
        return collect($users)->filter()->unique('id')->values()
            ->map(fn($u) => $this->orang($u))->all();
    }

    /**
     * Orang untuk avatar satu surat:
     * 1. Penerima disposisi TERAKHIR (ujung rantai, yang tidak meneruskan lagi ke orang lain).
     *    Contoh: Pimpinan -> Achmad -> (Albi, Bashar, Tria)  =>  Albi, Bashar, Tria.
     * 2. Ditambah penerima yang menandai "ikut" (mis. Eselon II yang meneruskan tapi tetap hadir), diberi flag ikut.
     */
    private function orangSurat($surat): array
    {
        $semua = $surat?->disposisi ?? collect();
        $peneruslah = $semua->pluck('dari_user_id')->filter()->unique();

        $akhir = $semua->reject(fn($x) => $peneruslah->contains($x->kepada_user_id));
        if ($akhir->isEmpty()) {
            $akhir = $semua;   // jaga-jaga kalau rantainya aneh
        }

        $daftar = $this->daftarOrang($akhir->map(fn($x) => $x->kepada));
        $sudah = collect($daftar)->pluck('id');

        foreach ($semua->where('ikut', true)->pluck('kepada')->filter()->unique('id') as $u) {
            if (!$sudah->contains($u->id)) {
                $daftar[] = $this->orang($u) + ['ikut' => true];
            }
        }

        return array_values($daftar);
    }

    /** Empat warna saja: baru, dibaca, selesai, umum (bukan dispo ke saya). */
    private function kelasWarna(array $r): string
    {
        if ($r['arah'] === 'umum') {
            return 'umum';
        }

        return match ($r['status']) {
            'baru' => 'baru',
            'selesai' => 'selesai',
            default => 'dibaca',   // dibaca, diteruskan, keluar
        };
    }

    /**
     * Ringkasan satu surat dari sudut pandang user:
     * - penerima  : status = status disposisi saya (yang paling butuh tindakan)
     * - pengirim  : selesai bila semua disposisi yang saya kirim selesai, selain itu "keluar"
     * - lainnya   : status surat (selesai / proses)
     * Kalau saya penerima sekaligus pengirim (meneruskan), tampil sebagai penerima.
     */
    private function ringkas(DisposisiSurat $s, $user): array
    {
        $semua = $s->disposisi;
        $milik = $semua->filter(fn($d) => (int) $d->kepada_user_id === (int) $user->id);
        $kirim = $semua->filter(fn($d) => (int) $d->dari_user_id === (int) $user->id);

        $dasar = [
            'surat_id' => $s->id,
            'surat' => $s,
            'saya_masuk' => $milik->isNotEmpty(),
            'saya_kirim' => $kirim->isNotEmpty(),
            'perlu' => $milik->contains(fn($d) => in_array($d->status, Disposisi::TERBUKA, true)),
            'orang_users' => $this->orangSurat($s),
        ];

        if ($milik->isNotEmpty()) {
            $d = $milik->sortBy(fn($x) => self::URUT[$x->status] ?? 9)->first();

            return $dasar + [
                'arah' => 'masuk',
                'status' => $d->status,
                'status_label' => $d->status_label,
                'sub' => 'Dari ' . ($d->dari?->name ?? '-'),
                'instruksi' => $d->instruksi_label,
                'terlambat' => $d->terlambat,
                'batas' => $d->batas_waktu,
            ];
        }

        if ($kirim->isNotEmpty()) {
            $selesai = $kirim->every(fn($x) => $x->status === 'selesai');
            $nama = $kirim->map(fn($x) => $x->kepada?->name)->filter()->unique()->values();
            $d = $kirim->first();

            return $dasar + [
                'arah' => 'keluar',
                'status' => $selesai ? 'selesai' : 'keluar',
                'status_label' => $selesai ? 'Selesai' : $kirim->where('status', 'selesai')->count() . '/' . $kirim->count() . ' selesai',
                'sub' => 'Ke ' . $nama->take(3)->implode(', ') . ($nama->count() > 3 ? ' +' . ($nama->count() - 3) : ''),
                'instruksi' => $d->instruksi_label,
                'terlambat' => false,
                'batas' => $d->batas_waktu,
            ];
        }

        // Tidak terlibat: ikut status surat
        $selesai = $s->status === 'selesai';

        return $dasar + [
            'arah' => 'umum',
            'status' => $selesai ? 'selesai' : 'diteruskan',
            'status_label' => $selesai ? 'Selesai' : 'Proses',
            'sub' => $s->asal_surat ? 'Dari ' . $s->asal_surat : '',
            'instruksi' => [],
            'terlambat' => false,
            'batas' => null,
        ];
    }

    /** Kegiatan HARI INI (semua surat berjadwal) + disposisi saya yang masih "baru", urut jam. */
    private function rapatHariIni($user)
    {
        $uid = $user->id;

        $surat = DisposisiSurat::query()
            ->select('disposisi_surat.*', DB::raw(self::TGL . ' as tgl_kal'))
            ->where(function ($w) use ($uid) {
                $w->whereRaw(self::TGL . ' = ?', [now()->toDateString()])
                    ->orWhereHas('disposisi', fn($d) => $d->where('kepada_user_id', $uid)->where('status', 'baru'));
            })
            ->with(['disposisi.kepada', 'disposisi.dari'])
            ->get();

        return $surat
            ->map(function ($s) use ($user) {
                $r = $this->ringkas($s, $user);

                return $r + ['kelas' => $this->kelasWarna($r)];
            })
            // Surat tanpa jadwal hanya tampil untuk yang terlibat
            ->filter(fn($r) => $r['surat']->tanggal_kegiatan || $r['saya_masuk'] || $r['saya_kirim'])
            ->sortBy([
                fn($a, $b) => ($a['status'] === 'baru' ? 0 : 1) <=> ($b['status'] === 'baru' ? 0 : 1),
                fn($a, $b) => (string) ($a['surat']?->waktu_mulai ?? '99') <=> (string) ($b['surat']?->waktu_mulai ?? '99'),
            ])
            ->values();
    }

    private function event(DisposisiSurat $s, array $r, string $judul): array
    {
        $tgl = Carbon::parse($s->tgl_kal)->toDateString();
        $ev = [
            'id' => 'surat-' . $s->id,
            'title' => $judul,
            // Warna diatur lewat CSS di halaman (ev-baru, ev-dibaca, ev-selesai, ev-umum)
            'classNames' => ['ev', 'w-' . $this->kelasWarna($r)],
            'extendedProps' => [
                'surat_id' => $s->id,
                'arah' => $r['arah'],
                'sub' => $r['sub'],
                'tempat' => $s->tempat,
                'sifat' => $s->sifat,
                'orang' => $r['orang_users'],   // [{id, nama, foto, ikut?}] -> dipakai untuk avatar di event
            ],
        ];

        if ($s->tanggal_kegiatan && $s->waktu_mulai) {
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