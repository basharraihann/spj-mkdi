<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SuratMasukController extends Controller
{
    /**
     * Tampilkan daftar monitoring surat masuk.
     */
    public function index(Request $request): View
    {
        $query = SuratMasuk::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('perihal', 'like', "%{$search}%")
                    ->orWhere('asal_surat', 'like', "%{$search}%")
                    ->orWhere('nomor_agenda', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%");
            });
        }

        $suratMasuk = $query->latest()->paginate(10)->withQueryString();

        return view('surat-masuk.index', compact('suratMasuk'));
    }

    /**
     * Form tambah Surat Masuk baru (Khusus TU & Admin).
     */
    public function create(): View
    {
        $this->authorizeAdminOrTu();

        return view('surat-masuk.create');
    }

    /**
     * Simpan Surat Masuk baru & buat disposisi awal ke Ibu Deputi 2.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdminOrTu();

        $validated = $request->validate([
            'nomor_agenda' => ['required', 'string', 'max:100', 'unique:surat_masuk,nomor_agenda'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => ['nullable', 'date'],
            'drive_link' => ['nullable', 'url', 'max:500'],
        ]);

        DB::transaction(function () use ($validated) {
            $user = Auth::user();

            $surat = SuratMasuk::create([
                'nomor_agenda' => $validated['nomor_agenda'],
                'nomor_surat' => $validated['nomor_surat'] ?? '-',
                'asal_surat' => $validated['asal_surat'],
                'perihal' => $validated['perihal'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'tanggal_diterima' => $validated['tanggal_diterima'] ?? now()->format('Y-m-d'),
                'drive_link' => $validated['drive_link'] ?? null,
                'created_by_id' => $user->id,
                'status' => 'baru',
            ]);

            // KUNCI PRESISI: Selalu cari akun "Ibu Deputi 2" yang sedang kamu gunakan (username deputi-d2 / name Ibu Deputi 2)
            $deputiUser = User::where('username', 'deputi-d2')
                ->orWhere('name', 'like', '%Ibu Deputi 2%')
                ->orWhere('role', 'deputi-d2')
                ->first();

            // Fallback jika tidak ditemukan
            if (!$deputiUser) {
                $deputiUser = User::whereIn('role', ['eselon1', 'deputi'])->first();
            }

            if ($deputiUser) {
                Disposisi::create([
                    'surat_masuk_id' => $surat->id,
                    'parent_dispo_id' => null,
                    'pengirim_id' => $user->id,
                    'penerima_id' => $deputiUser->id, // Tepat terkirim ke ID Ibu Deputi 2
                    'instruksi' => 'Surat baru diterima. Mohon petunjuk dan arahan Ibu Deputi.',
                    'sifat' => 'biasa',
                    'status' => 'baru',
                ]);
            }
        });

        return redirect()->route('surat-masuk.index')->with('success', 'Surat Masuk berhasil dicatat dan diteruskan ke Ibu Deputi.');
    }

    /**
     * Detail Surat Masuk & riwayat rantai disposisinya.
     */
    public function show(SuratMasuk $suratMasuk): View
    {
        $suratMasuk->load(['disposisi.pengirim', 'disposisi.penerima', 'createdBy']);

        return view('surat-masuk.show', compact('suratMasuk'));
    }

    /**
     * Form edit Surat Masuk.
     */
    public function edit(SuratMasuk $suratMasuk): View
    {
        $this->authorizeAdminOrTu();

        return view('surat-masuk.edit', compact('suratMasuk'));
    }

    /**
     * Update data Surat Masuk.
     */
    public function update(Request $request, SuratMasuk $suratMasuk): RedirectResponse
    {
        $this->authorizeAdminOrTu();

        $validated = $request->validate([
            'nomor_agenda' => ['required', 'string', 'max:100', 'unique:surat_masuk,nomor_agenda,' . $suratMasuk->id],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'perihal' => ['required', 'string'],
            'tanggal_surat' => ['required', 'date'],
            'tanggal_diterima' => ['nullable', 'date'],
            'drive_link' => ['nullable', 'url', 'max:500'],
        ]);

        $suratMasuk->update([
            'nomor_agenda' => $validated['nomor_agenda'],
            'nomor_surat' => $validated['nomor_surat'] ?? '-',
            'asal_surat' => $validated['asal_surat'],
            'perihal' => $validated['perihal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tanggal_diterima' => $validated['tanggal_diterima'] ?? $suratMasuk->tanggal_diterima,
            'drive_link' => $validated['drive_link'] ?? null,
        ]);

        return redirect()->route('surat-masuk.index')->with('success', 'Data Surat Masuk berhasil diperbarui.');
    }

    /**
     * Hapus Surat Masuk beserta seluruh disposisi terkait.
     */
    public function destroy(SuratMasuk $suratMasuk): RedirectResponse
    {
        $this->authorizeAdminOrTu();

        DB::transaction(function () use ($suratMasuk) {
            Disposisi::where('surat_masuk_id', $suratMasuk->id)->delete();
            $suratMasuk->delete();
        });

        return redirect()->route('surat-masuk.index')->with('success', 'Surat Masuk dan seluruh rantai disposisi berhasil dihapus.');
    }

    /**
     * Helper Privasi Hak Akses Khusus Admin & TU
     */
    private function authorizeAdminOrTu(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Akses ditolak.');
        }

        $isAuthorized = $user->isAdmin() 
            || $user->isTu() 
            || in_array($user->role, ['admin', 'tu', 'tu-d2', 'admin-d2'], true);

        if (!$isAuthorized) {
            abort(403, 'Hanya admin / TU yang boleh menginput atau mengelola surat masuk.');
        }
    }
}