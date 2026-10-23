<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DisposisiController extends Controller
{
    private const TAB = ['semua', 'baru', 'dibaca', 'diproses'];

    public function index(Request $request)
    {
        $userId = Auth::id();
        $tab = in_array($request->query('tab'), self::TAB, true) ? $request->query('tab') : 'semua';
        $q = trim((string) $request->query('q', ''));

        $dasar = Disposisi::where('penerima_id', $userId);

        $perStatus = (clone $dasar)->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $jumlah = [
            'semua' => (int) $perStatus->sum(),
            'baru' => (int) ($perStatus['baru'] ?? 0),
            'dibaca' => (int) ($perStatus['dibaca'] ?? 0),
            'diproses' => (int) ($perStatus['diproses'] ?? 0),
        ];

        $disposisi = (clone $dasar)
            ->with(['suratMasuk', 'pengirim'])
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

    public function show($id)
    {
        $disposisi = Disposisi::with(['suratMasuk', 'pengirim', 'penerima'])->findOrFail($id);

        abort_unless($this->bolehMelihat($disposisi), 403);

        if ((int) $disposisi->penerima_id === (int) Auth::id() && $disposisi->status === 'baru') {
            $disposisi->update([
                'status' => 'dibaca',
                'dibaca_at' => now(),
            ]);
        }

        $byParent = Disposisi::where('surat_masuk_id', $disposisi->surat_masuk_id)
            ->with(['pengirim', 'penerima'])
            ->orderBy('id')
            ->get()
            ->groupBy(fn($d) => $d->parent_dispo_id ?? 0);

        // Staf / Pegawai Pelaksana (role user/pegawai/pegawai-d2) TIDAK BOLEH mendisposisikan lanjutan
        $currentUser = Auth::user();
        $isStaff = in_array($currentUser->role, ['user', 'pegawai', 'pegawai-d2'], true);

        $bolehDisposisi = $this->bolehMeneruskan($disposisi) && !$isStaff;

        $users = $bolehDisposisi
            ? User::with('pegawai:id,jabatan,unit_kerja')
                ->where('id', '!=', Auth::id())
                ->when(in_array($currentUser->role, ['eselon1', 'deputi', 'deputi-d2'], true), function ($q) {
                    $q->whereIn('role', ['eselon2', 'user', 'asdep', 'pegawai', 'asdep-d2', 'pegawai-d2']);
                })
                ->when(in_array($currentUser->role, ['eselon2', 'asdep', 'asdep-d2'], true), function ($q) {
                    $q->whereIn('role', ['user', 'pegawai', 'pegawai-d2']);
                })
                ->when(in_array($currentUser->role, ['tu', 'tu-d2'], true), function ($q) {
                    $q->whereIn('role', ['eselon1', 'deputi', 'deputi-d2']);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'role', 'pegawai_id'])
            : collect();

        return view('disposisi.show', compact('disposisi', 'users', 'byParent', 'bolehDisposisi', 'isStaff'));
    }

    public function store(Request $request, $id): RedirectResponse
    {
        $parentDispo = Disposisi::findOrFail($id);

        if ($parentDispo->status === 'diproses') {
            return redirect()->route('disposisi.show', $id)
                ->with('error', 'Disposisi ini sudah pernah Anda kirimkan.');
        }

        abort_unless($this->bolehMeneruskan($parentDispo), 403);

        $validated = $request->validate([
            'penerima_ids' => ['required', 'array', 'min:1'],
            'penerima_ids.*' => ['integer', 'distinct', 'exists:users,id', Rule::notIn([Auth::id()])],
            'instruksi' => ['required', 'string'],
            'tenggat_waktu' => ['nullable', 'date'],
            'sifat' => ['required', 'in:biasa,segera,rahasia'],
            'perlu_st' => ['nullable', 'boolean'],
        ], [
            'penerima_ids.required' => 'Pilih minimal satu penerima disposisi.',
            'penerima_ids.min' => 'Pilih minimal satu penerima disposisi.',
            'penerima_ids.*.not_in' => 'Anda tidak bisa mendisposisikan ke diri sendiri.',
            'instruksi.required' => 'Instruksi / Catatan Tugas wajib diisi.',
        ]);

        DB::transaction(function () use ($validated, $parentDispo, $request) {
            $currentUser = Auth::user();

            foreach ($validated['penerima_ids'] as $penerimaId) {
                // Buat disposisi utama
                $dispo = Disposisi::create([
                    'surat_masuk_id' => $parentDispo->surat_masuk_id,
                    'parent_dispo_id' => $parentDispo->id,
                    'pengirim_id' => Auth::id(),
                    'penerima_id' => $penerimaId,
                    'instruksi' => $validated['instruksi'],
                    'tenggat_waktu' => $validated['tenggat_waktu'] ?? null,
                    'sifat' => $validated['sifat'],
                    'perlu_st' => $request->boolean('perlu_st'),
                    'status' => 'baru',
                ]);

                // JALUR B FLOWCHART: Jika Deputi langsung mendisposisikan ke Pegawai (tanpa lewat Asdep)
                // Sistem otomatis mengirimkan tembusan disposisi ke Asdep terkait
                $targetUser = User::find($penerimaId);
                $isDeputi = in_array($currentUser->role, ['eselon1', 'deputi', 'deputi-d2'], true);
                $isPegawaiTarget = in_array($targetUser?->role, ['user', 'pegawai', 'pegawai-d2'], true);

                if ($isDeputi && $isPegawaiTarget) {
                    $asdepUser = User::whereIn('role', ['eselon2', 'asdep', 'asdep-d2'])->first();

                    if ($asdepUser && $asdepUser->id !== $penerimaId) {
                        Disposisi::create([
                            'surat_masuk_id' => $parentDispo->surat_masuk_id,
                            'parent_dispo_id' => $parentDispo->id,
                            'pengirim_id' => Auth::id(),
                            'penerima_id' => $asdepUser->id,
                            'instruksi' => '[TEMBUSAN JALUR B] ' . $validated['instruksi'],
                            'tenggat_waktu' => $validated['tenggat_waktu'] ?? null,
                            'sifat' => $validated['sifat'],
                            'perlu_st' => false, // Jalur B tanpa ST
                            'status' => 'baru',
                        ]);
                    }
                }
            }

            $parentDispo->update(['status' => 'diproses']);
        });

        return redirect()->route('disposisi.show', $id)->with(
            'success',
            'Disposisi diteruskan ke ' . count($validated['penerima_ids']) . ' penerima.'
        );
    }

    private function bolehMelihat(Disposisi $disposisi): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Semua user yang terautentikasi dan memiliki role valid diizinkan melihat lembar disposisi
        if ($user->isAdmin() || $user->isTu() || in_array($user->role, ['deputi-d2', 'asdep-d2', 'pegawai-d2', 'admin-d2', 'eselon1', 'eselon2', 'user', 'pegawai'], true)) {
            return true;
        }

        return Disposisi::where('surat_masuk_id', $disposisi->surat_masuk_id)
            ->where(fn($q) => $q->where('pengirim_id', $user->id)->orWhere('penerima_id', $user->id))
            ->exists();
    }

    private function bolehMeneruskan(Disposisi $disposisi): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // Boleh meneruskan jika penerima disposisi adalah user tersebut, atau bertindak sebagai admin/pejabat yang berhak
        return (int) $disposisi->penerima_id === (int) $user->id 
            || $user->isAdmin() 
            || in_array($user->role, ['deputi-d2', 'asdep-d2', 'tu-d2'], true);
    }
}