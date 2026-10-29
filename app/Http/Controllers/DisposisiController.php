<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DisposisiController extends Controller
{
    // Ganti const MODE dan seluruh method index() dengan ini:

    private const TAB = ['semua', 'baru', 'dibaca', 'diproses', 'selesai'];

    public function index(Request $request)
    {
        $user = Auth::user();

        $tab = in_array($request->query('tab'), self::TAB, true) ? $request->query('tab') : 'semua';
        $q = trim((string) $request->query('q', ''));

        // TU & admin melihat disposisi yang mereka kirim, yang lain melihat yang masuk ke mereka
        $dasar = ($user->isTu() || $user->isAdmin())
            ? Disposisi::where('pengirim_id', $user->id)
            : Disposisi::where('penerima_id', $user->id);

        // Angka di dropdown status
        $perStatus = (clone $dasar)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $jumlah = [
            'semua' => (int) $perStatus->sum(),
            'baru' => (int) ($perStatus['baru'] ?? 0),
            'dibaca' => (int) ($perStatus['dibaca'] ?? 0),
            'diproses' => (int) ($perStatus['diproses'] ?? 0),
            'selesai' => (int) ($perStatus['selesai'] ?? 0),
        ];

        $disposisi = (clone $dasar)
            ->with(['suratMasuk', 'pengirim', 'penerima', 'children.penerima'])
            ->when($tab !== 'semua', fn($query) => $query->where('status', $tab))
            ->when($q !== '', function ($query) use ($q) {
                $query->whereHas('suratMasuk', function ($s) use ($q) {
                    $s->where('perihal', 'like', "%{$q}%")
                        ->orWhere('nomor_agenda', 'like', "%{$q}%")
                        ->orWhere('asal_surat', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('disposisi.index', compact('disposisi', 'jumlah', 'tab', 'q'));
    }
    /**
     * Detail disposisi, pohon hierarki (sudah difilter sesuai role), form teruskan / lapor.
     */
    public function show($id)
    {
        $user = Auth::user();
        $disposisi = Disposisi::with(['suratMasuk', 'pengirim', 'penerima'])->findOrFail($id);

        abort_unless($this->bolehMelihat($disposisi, $user), 403);

        if ((int) $disposisi->penerima_id === (int) $user->id && $disposisi->status === 'baru') {
            $disposisi->update([
                'status' => 'dibaca',
                'dibaca_at' => now(),
            ]);
        }

        $byParent = $this->susunPohon($disposisi, $user);

        $isStaff = $user->isUser();
        $bolehDisposisi = $this->bolehMeneruskan($disposisi, $user)
            && in_array($disposisi->status, ['baru', 'dibaca'], true);
        $bolehLapor = $this->bolehLapor($disposisi, $user);

        $users = $bolehDisposisi
            ? $this->queryPenerima($user)
                ->with(['pegawai:id,jabatan,unit_kerja', 'unit:id,kode,nama'])
                ->orderBy('unit_id')
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'pegawai_id', 'unit_id'])
            : collect();

        $laporanList = $this->daftarLaporan($disposisi, $user);

        return view('disposisi.show', compact(
            'disposisi',
            'users',
            'byParent',
            'bolehDisposisi',
            'bolehLapor',
            'isStaff',
            'laporanList'
        ));
    }

    /**
     * Teruskan disposisi ke bawahan.
     * Eselon 1 -> Eselon 2. Eselon 2 -> Pegawai di unit yang sama.
     * Setiap penerima bisa diberi petunjuk / catatan / batas waktu / sifat berbeda
     * lewat input `khusus[ID_PENERIMA]`. Tanpa itu, ikut pengaturan umum.
     */
    public function store(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $parentDispo = Disposisi::findOrFail($id);

        abort_unless($this->bolehMeneruskan($parentDispo, $user), 403);

        $validated = $request->validate([
            'penerima_ids' => ['required', 'array', 'min:1'],
            'penerima_ids.*' => ['integer', 'distinct'],
            'petunjuk' => ['nullable', 'array'],
            'petunjuk.*' => ['string', Rule::in(Disposisi::PETUNJUK)],
            'instruksi' => ['nullable', 'string'],
            'tenggat_waktu' => ['nullable', 'date'],
            'sifat' => ['required', 'in:biasa,segera,rahasia'],
            'khusus' => ['nullable', 'array'],
            'khusus.*.aktif' => ['nullable', 'boolean'],
            'khusus.*.petunjuk' => ['nullable', 'array'],
            'khusus.*.petunjuk.*' => ['string', Rule::in(Disposisi::PETUNJUK)],
            'khusus.*.instruksi' => ['nullable', 'string'],
            'khusus.*.tenggat_waktu' => ['nullable', 'date'],
            'khusus.*.sifat' => ['nullable', 'in:biasa,segera,rahasia'],
        ], [
            'penerima_ids.required' => 'Pilih minimal satu penerima disposisi.',
            'penerima_ids.min' => 'Pilih minimal satu penerima disposisi.',
            'petunjuk.*.in' => 'Petunjuk yang dipilih tidak valid.',
            'khusus.*.petunjuk.*.in' => 'Petunjuk khusus yang dipilih tidak valid.',
        ]);

        $ids = array_map('intval', $validated['penerima_ids']);
        $penerimaList = $this->queryPenerima($user)->whereIn('id', $ids)->get()->keyBy('id');

        if ($penerimaList->count() !== count($ids)) {
            throw ValidationException::withMessages([
                'penerima_ids' => 'Ada penerima yang tidak diizinkan untuk peran Anda.',
            ]);
        }

        $umumPetunjuk = array_values($validated['petunjuk'] ?? []);
        $umumCatatan = trim($validated['instruksi'] ?? '');
        $rencana = [];

        foreach ($penerimaList as $penerima) {
            $k = $validated['khusus'][$penerima->id] ?? [];
            $khusus = !empty($k['aktif']);

            $petunjuk = $khusus ? array_values($k['petunjuk'] ?? []) : $umumPetunjuk;
            $catatan = $khusus ? trim($k['instruksi'] ?? '') : $umumCatatan;

            if ($petunjuk === [] && $catatan === '') {
                throw ValidationException::withMessages([
                    'penerima_ids' => "Untuk {$penerima->name}: centang minimal satu petunjuk atau isi catatan.",
                ]);
            }

            $rencana[$penerima->id] = [
                'petunjuk' => $petunjuk,
                'instruksi' => $catatan !== '' ? $catatan : implode(', ', $petunjuk),
                'tenggat_waktu' => ($khusus ? ($k['tenggat_waktu'] ?? null) : null) ?: ($validated['tenggat_waktu'] ?? null),
                'sifat' => ($khusus ? ($k['sifat'] ?? null) : null) ?: $validated['sifat'],
            ];
        }

        $berhasil = DB::transaction(function () use ($parentDispo, $user, $penerimaList, $rencana) {
            $induk = Disposisi::whereKey($parentDispo->id)->lockForUpdate()->first();

            if (!$induk || !in_array($induk->status, ['baru', 'dibaca'], true)) {
                return false;
            }

            foreach ($penerimaList as $penerima) {
                $r = $rencana[$penerima->id];

                Disposisi::create([
                    'surat_masuk_id' => $induk->surat_masuk_id,
                    'parent_dispo_id' => $induk->id,
                    'pengirim_id' => $user->id,
                    'penerima_id' => $penerima->id,
                    'instruksi' => $r['instruksi'],
                    'petunjuk' => $r['petunjuk'] ?: null,
                    'tenggat_waktu' => $r['tenggat_waktu'],
                    'sifat' => $r['sifat'],
                    'perlu_st' => false,
                    'is_tembusan' => false,
                    'status' => 'baru',
                ]);
            }

            $induk->update(['status' => 'diproses']);

            return true;
        });

        if (!$berhasil) {
            return back()->with('error', 'Disposisi ini sudah diteruskan sebelumnya.');
        }

        return back()->with('success', 'Disposisi berhasil diteruskan.');
    }

    public function laporan(Request $request, $id): RedirectResponse
    {
        $user = Auth::user();
        $disposisi = Disposisi::findOrFail($id);

        abort_unless($this->bolehLapor($disposisi, $user), 403);

        $validated = $request->validateWithBag('laporan', [
            'isi' => ['required', 'string'],
            'drive_link' => ['nullable', 'url', 'max:500'],
        ], [
            'isi.required' => 'Isi laporan wajib diisi.',
            'drive_link.url' => 'Link berkas laporan tidak valid.',
        ]);

        DB::transaction(function () use ($validated, $disposisi, $user) {
            Laporan::create([
                'disposisi_id' => $disposisi->id,
                'pelapor_id' => $user->id,
                'isi' => $validated['isi'],
                'drive_link' => $validated['drive_link'] ?? null,
            ]);

            $disposisi->update(['status' => 'selesai']);

            $this->selesaikanInduk($disposisi);
        });

        return back()->with('success', 'Laporan berhasil dikirim.');
    }

    /**
     * Naikkan status selesai ke atas rantai.
     * Induk menjadi selesai bila semua anaknya (selain tembusan) sudah selesai.
     * Berlanjut ke induk berikutnya (Asdep -> Deputi) selama syarat itu terpenuhi.
     */
    private function selesaikanInduk(Disposisi $disposisi): void
    {
        $saatIni = $disposisi;

        while ($saatIni->parent_dispo_id) {
            $induk = Disposisi::whereKey($saatIni->parent_dispo_id)->lockForUpdate()->first();

            if (!$induk || $induk->status === 'selesai') {
                break;
            }

            $adaBelumSelesai = Disposisi::where('parent_dispo_id', $induk->id)
                ->where('is_tembusan', false)
                ->where('status', '!=', 'selesai')
                ->exists();

            if ($adaBelumSelesai) {
                break;
            }

            $induk->update(['status' => 'selesai']);
            $saatIni = $induk;
        }
    }

    // ------------------------------------------------------------------
    // Aturan akses & visibilitas
    // ------------------------------------------------------------------

    /**
     * Apakah satu node disposisi boleh terlihat oleh viewer.
     * - Admin: semua.
     * - TU & Eselon 1: semua kecuali disposisi yang penerimanya Pegawai (hierarki berhenti di Asdep).
     * - Eselon 2: disposisi ke Pegawai hanya yang di unitnya sendiri.
     * - Pegawai: hanya disposisi yang ditujukan ke dirinya.
     */
    private function nodeTerlihat(Disposisi $d, User $viewer): bool
    {
        $penerima = $d->penerima;
        $penerimaStaf = $penerima && $penerima->role === User::ROLE_USER;

        return match (true) {
            $viewer->isAdmin() => true,
            $viewer->isTu(), $viewer->isEselon1() => !$penerimaStaf,
            $viewer->isEselon2() => !$penerimaStaf || (int) $penerima->unit_id === (int) $viewer->unit_id,
            default => (int) $d->penerima_id === (int) $viewer->id,
        };
    }

    private function bolehMelihat(Disposisi $d, User $user): bool
    {
        if (!$this->nodeTerlihat($d, $user)) {
            return false;
        }

        if ($user->isAdmin() || $user->isEselon1()) {
            return true;
        }

        if ($user->isTu()) {
            $surat = $d->suratMasuk;

            return $surat && (
                (int) $surat->created_by_id === (int) $user->id
                || ($user->unit_id && (int) $surat->unit_id === (int) $user->unit_id)
            );
        }

        if ($user->isEselon2()) {
            return Disposisi::where('surat_masuk_id', $d->surat_masuk_id)
                ->where(fn($q) => $q->where('pengirim_id', $user->id)->orWhere('penerima_id', $user->id))
                ->exists();
        }

        return (int) $d->penerima_id === (int) $user->id;
    }

    /** Hanya penerima (Eselon 1 / Eselon 2) yang boleh meneruskan, dan bukan untuk tembusan. */
    private function bolehMeneruskan(Disposisi $d, User $user): bool
    {
        return (int) $d->penerima_id === (int) $user->id
            && !$d->is_tembusan
            && ($user->isEselon1() || $user->isEselon2());
    }

    /**
     * Hanya penerima yang boleh lapor.
     * - Pegawai: boleh kapan saja (termasuk laporan revisi).
     * - Eselon 2: hanya bila belum meneruskan ke pegawai (status baru / dibaca).
     */
    private function bolehLapor(Disposisi $d, User $user): bool
    {
        if ((int) $d->penerima_id !== (int) $user->id || $d->is_tembusan) {
            return false;
        }

        if ($user->isUser()) {
            return true;
        }

        if ($user->isEselon2()) {
            return in_array($d->status, ['baru', 'dibaca'], true);
        }

        return false;
    }

    /** Daftar calon penerima disposisi sesuai role pengirim. */
    private function queryPenerima(User $user): Builder
    {
        $q = User::query()->where('id', '!=', $user->id);

        if ($user->isEselon1()) {
            return $q->where('role', User::ROLE_ESELON2);
        }

        if ($user->isEselon2() && $user->unit_id) {
            return $q->where('role', User::ROLE_USER)->where('unit_id', $user->unit_id);
        }

        return $q->whereRaw('1 = 0');
    }

    /** Pohon disposisi untuk satu surat, sudah difilter sesuai viewer. */
    private function susunPohon(Disposisi $disposisi, User $user): Collection
    {
        if ($user->isUser()) {
            return collect([0 => collect([$disposisi])]);
        }

        return Disposisi::where('surat_masuk_id', $disposisi->surat_masuk_id)
            ->with(['pengirim', 'penerima'])
            ->orderBy('id')
            ->get()
            ->filter(fn($d) => $this->nodeTerlihat($d, $user))
            ->groupBy(fn($d) => $d->parent_dispo_id ?? 0);
    }

    /**
     * Identitas pegawai hanya terlihat oleh admin, pegawai itu sendiri, dan Asdep di unitnya.
     * Pihak lain (TU, Eselon 1, Asdep unit lain) hanya tahu laporan datang dari Asdep.
     */
    private function bolehLihatIdentitasPegawai(User $pegawai, User $viewer): bool
    {
        return $viewer->isAdmin()
            || (int) $viewer->id === (int) $pegawai->id
            || ($viewer->isEselon2() && (int) $viewer->unit_id === (int) $pegawai->unit_id);
    }

    /**
     * Laporan untuk surat ini, sesuai viewer.
     * - Admin, Eselon 1, TU: semua. Identitas pelapor Pegawai disamarkan menjadi Asdep unitnya,
     *   kecuali untuk admin, pegawai itu sendiri, dan Asdep di unit yang sama.
     * - Eselon 2: laporan miliknya + laporan pegawai di unitnya.
     * - Pegawai: laporan miliknya.
     */
    private function daftarLaporan(Disposisi $disposisi, User $viewer): Collection
    {
        $q = Laporan::with('pelapor:id,name,role,unit_id')
            ->whereHas('disposisi', fn($d) => $d->where('surat_masuk_id', $disposisi->surat_masuk_id))
            ->latest();

        if ($viewer->isUser()) {
            $q->where('pelapor_id', $viewer->id);
        } elseif ($viewer->isEselon2()) {
            $q->where(fn($w) => $w->where('pelapor_id', $viewer->id)
                ->orWhereHas('pelapor', fn($p) => $p->where('unit_id', $viewer->unit_id)));
        }

        $asdepPerUnit = User::where('role', User::ROLE_ESELON2)->pluck('name', 'unit_id');

        return $q->get()->map(function (Laporan $l) use ($viewer, $asdepPerUnit) {
            $nama = $l->pelapor->name ?? '-';

            if ($l->pelapor?->role === User::ROLE_USER && !$this->bolehLihatIdentitasPegawai($l->pelapor, $viewer)) {
                $nama = $asdepPerUnit[$l->pelapor->unit_id] ?? 'Asdep terkait';
            }

            return [
                'nama' => $nama,
                'isi' => $l->isi,
                'drive_link' => $l->drive_link,
                'waktu' => $l->created_at,
            ];
        });
    }
}