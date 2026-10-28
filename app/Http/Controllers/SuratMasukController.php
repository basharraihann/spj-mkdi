<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\SuratMasuk;
use App\Models\Unit;
use App\Models\User;
use App\Services\SuratMasukNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SuratMasukController extends Controller
{
    /**
     * Daftar monitoring surat masuk.
     * TU hanya melihat surat unitnya sendiri. Admin & role lain melihat semua.
     */
    public function index(Request $request, SuratMasukNumberService $numbering): View
    {
        $user = Auth::user();

        // Dasar query sesuai hak akses (dipakai juga untuk hitungan chip status)
        $base = $this->batasiAkses(SuratMasuk::query(), $user);

        $statusCounts = (clone $base)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $query = clone $base;

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('perihal', 'like', "%{$search}%")
                    ->orWhere('asal_surat', 'like', "%{$search}%")
                    ->orWhere('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $suratMasuk = $query
            ->with('disposisi.penerima')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Pratinjau nomor agenda hanya relevan untuk yang bisa menginput surat
        $nomorAgendaBerikutnya = ($user->isAdmin() || $user->isTu())
            ? $numbering->nomorBerikutnya()
            : null;

        return view('surat-masuk.index', compact('suratMasuk', 'statusCounts', 'nomorAgendaBerikutnya'));
    }

    /**
     * Form tambah Surat Masuk (TU & Admin).
     * TU: penerima otomatis dari unitnya. Admin: pilih unit tujuan.
     */
    public function create(SuratMasukNumberService $numbering): View
    {
        $this->authorizeAdminOrTu();

        $user = Auth::user();

        $target = null;
        $unitNama = null;
        $units = collect();

        if ($user->unit_id) {
            $target = $this->findPenerima($user->unit_id);
            $unitNama = Unit::where('id', $user->unit_id)->value('nama');
        } else {
            $units = Unit::orderBy('id')->get();
        }

        $nomorAgendaBerikutnya = $numbering->nomorBerikutnya();

        return view('surat-masuk.create', compact(
            'target',
            'unitNama',
            'units',
            'nomorAgendaBerikutnya'
        ));
    }

    /**
     * Simpan Surat Masuk & buat disposisi awal ke pejabat (Eselon 1/2) di unit yang sama.
     * Jalur & nomor agenda ditentukan server, bukan dari input form.
     */
    public function store(Request $request, SuratMasukNumberService $numbering): RedirectResponse
    {
        $this->authorizeAdminOrTu();

        $validated = $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => ['nullable', 'date', 'after_or_equal:tanggal_surat'],
            'drive_link' => ['nullable', 'url', 'max:500'],
            'unit_id' => ['nullable', 'exists:units,id'], // hanya dipakai admin
        ]);

        DB::transaction(function () use ($validated, $numbering) {
            $user = Auth::user();

            // TU selalu pakai unitnya sendiri (input unit_id dari TU diabaikan)
            $unitId = $user->unit_id ?? ($validated['unit_id'] ?? null);

            if (!$unitId) {
                throw ValidationException::withMessages([
                    'unit_id' => 'Unit tujuan belum dipilih.',
                ]);
            }

            $targetUser = $this->findPenerima($unitId);

            if (!$targetUser) {
                throw ValidationException::withMessages([
                    'unit_id' => 'Belum ada pejabat (Eselon 1/2) terdaftar di unit ini.',
                ]);
            }

            $jalur = $targetUser->role === 'eselon1' ? 'deputi' : 'eselon2';

            // Nomor agenda diambil dari counter (dikunci di dalam transaksi, anti bentrok)
            $surat = SuratMasuk::create([
                'nomor_agenda' => $numbering->ambil(),
                'nomor_surat' => $validated['nomor_surat'] ?? '-',
                'asal_surat' => $validated['asal_surat'],
                'perihal' => $validated['perihal'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'tanggal_diterima' => $validated['tanggal_diterima'] ?? now()->format('Y-m-d'),
                'jalur_tujuan' => $jalur,
                'drive_link' => $validated['drive_link'] ?? null,
                'unit_id' => $unitId,
                'created_by_id' => $user->id,
                'status' => 'baru',
            ]);

            Disposisi::create([
                'surat_masuk_id' => $surat->id,
                'parent_dispo_id' => null,
                'pengirim_id' => $user->id,
                'penerima_id' => $targetUser->id,
                'instruksi' => 'Surat baru diterima. Mohon petunjuk dan arahan.',
                'sifat' => 'biasa',
                'status' => 'baru',
            ]);
        });

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat Masuk berhasil dicatat dan diteruskan.');
    }

    /**
     * Detail Surat Masuk & riwayat rantai disposisinya.
     */
    public function show(SuratMasuk $suratMasuk): View
    {
        $user = Auth::user();

        abort_unless(
            $this->batasiAkses(SuratMasuk::whereKey($suratMasuk->id), $user)->exists(),
            403,
            'Anda tidak memiliki akses ke surat ini.'
        );

        // Hierarki disaring sesuai role: TU berhenti di Asdep, Asdep hanya pegawai unitnya,
        // Pegawai hanya disposisi miliknya.
        $suratMasuk->load([
            'disposisi' => function ($q) use ($user) {
                if ($user->isTu() || $user->isEselon1()) {
                    $q->whereHas('penerima', fn($p) => $p->where('role', '!=', 'user'));
                } elseif ($user->isEselon2()) {
                    $q->whereHas('penerima', fn($p) => $p->where('role', '!=', 'user')
                        ->orWhere('unit_id', $user->unit_id));
                } elseif ($user->isUser()) {
                    $q->where('penerima_id', $user->id);
                }
            },
            'disposisi.pengirim',
            'disposisi.penerima',
            'createdBy',
        ]);

        return view('surat-masuk.show', compact('suratMasuk'));
    }

    /**
     * Form edit Surat Masuk.
     */
    public function edit(SuratMasuk $suratMasuk): View|RedirectResponse
    {
        $this->authorizeAdminOrTu();
        $this->authorizeSameUnit($suratMasuk);

        if ($blokir = $this->blokirPerubahan($suratMasuk)) {
            return $blokir;
        }

        return view('surat-masuk.edit', compact('suratMasuk'));
    }

    /**
     * Update data Surat Masuk.
     * Nomor agenda, jalur, dan unit tidak bisa diubah (disposisi awal sudah terbentuk).
     */
    public function update(Request $request, SuratMasuk $suratMasuk): RedirectResponse
    {
        $this->authorizeAdminOrTu();
        $this->authorizeSameUnit($suratMasuk);

        if ($blokir = $this->blokirPerubahan($suratMasuk)) {
            return $blokir;
        }

        $validated = $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => ['nullable', 'date', 'after_or_equal:tanggal_surat'],
            'drive_link' => ['nullable', 'url', 'max:500'],
        ]);

        $suratMasuk->update([
            'nomor_surat' => $validated['nomor_surat'] ?? '-',
            'asal_surat' => $validated['asal_surat'],
            'perihal' => $validated['perihal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_diterima' => $validated['tanggal_diterima'] ?? $suratMasuk->tanggal_diterima,
            'drive_link' => $validated['drive_link'] ?? null,
        ]);

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Data Surat Masuk berhasil diperbarui.');
    }

    /**
     * Hapus Surat Masuk beserta seluruh disposisi terkait.
     */
    public function destroy(SuratMasuk $suratMasuk): RedirectResponse
    {
        $this->authorizeAdminOrTu();
        $this->authorizeSameUnit($suratMasuk);

        // Surat yang sudah punya laporan tidak boleh dihapus siapa pun (termasuk admin)
        $adaLaporan = Laporan::whereHas('disposisi', fn($d) => $d->where('surat_masuk_id', $suratMasuk->id))->exists();

        if ($adaLaporan) {
            return redirect()->route('surat-masuk.index')
                ->with('error', 'Surat sudah memiliki laporan pekerjaan sehingga tidak dapat dihapus.');
        }

        if ($blokir = $this->blokirPerubahan($suratMasuk)) {
            return $blokir;
        }

        DB::transaction(function () use ($suratMasuk) {
            Disposisi::where('surat_masuk_id', $suratMasuk->id)->delete();
            $suratMasuk->delete();
        });

        return redirect()->route('surat-masuk.index')
            ->with('success', 'Surat Masuk dan seluruh rantai disposisi berhasil dihapus.');
    }

    /**
     * Pejabat penerima disposisi awal di sebuah unit (Eselon 1 atau Eselon 2).
     */
    private function findPenerima(int $unitId): ?User
    {
        return User::where('unit_id', $unitId)
            ->whereIn('role', ['eselon1', 'eselon2'])
            ->orderBy('id')
            ->first();
    }

    /**
     * TU tidak boleh mengubah / menghapus surat yang sudah dibaca atau diproses penerima.
     * Admin bebas mengubah. Mengembalikan redirect bila diblokir, null bila boleh.
     */
    private function blokirPerubahan(SuratMasuk $surat): ?RedirectResponse
    {
        if (Auth::user()->isAdmin()) {
            return null;
        }

        $sudahDiproses = Disposisi::where('surat_masuk_id', $surat->id)
            ->where('status', '!=', 'baru')
            ->exists();

        return $sudahDiproses
            ? redirect()->route('surat-masuk.index')
                ->with('error', 'Surat sudah dibaca / diproses penerima sehingga tidak dapat diubah atau dihapus.')
            : null;
    }

    /**
     * Batasi surat yang boleh dilihat sesuai role.
     * - Admin & Eselon 1: semua.
     * - TU: surat unitnya sendiri.
     * - Eselon 2 & Pegawai: hanya surat yang melibatkan mereka di rantai disposisi.
     */
    private function batasiAkses($query, User $user)
    {
        if ($user->isAdmin() || $user->isEselon1()) {
            return $query;
        }

        if ($user->isTu()) {
            return $query->where(function ($q) use ($user) {
                $q->where('created_by_id', $user->id);

                if ($user->unit_id) {
                    $q->orWhere('unit_id', $user->unit_id);
                }
            });
        }

        return $query->whereHas('disposisi', fn($d) => $d->where(
            fn($w) => $w->where('penerima_id', $user->id)->orWhere('pengirim_id', $user->id)
        ));
    }

    /**
     * TU hanya boleh mengelola surat unitnya sendiri. Admin bebas.
     */
    private function authorizeSameUnit(SuratMasuk $suratMasuk): void
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return;
        }

        if ((int) $suratMasuk->created_by_id === (int) $user->id) {
            return;
        }

        if ($user->unit_id && (int) $suratMasuk->unit_id !== (int) $user->unit_id) {
            abort(403, 'Surat ini bukan milik unit Anda.');
        }
    }

    /**
     * Hak akses khusus Admin & TU.
     */
    private function authorizeAdminOrTu(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $isAuthorized = $user->isAdmin()
            || $user->isTu()
            || in_array($user->role, ['admin', 'tu'], true);

        if (!$isAuthorized) {
            abort(403, 'Hanya admin / TU yang boleh menginput atau mengelola surat masuk.');
        }
    }
}