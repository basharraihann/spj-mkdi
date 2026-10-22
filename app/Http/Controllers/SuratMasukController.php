<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Services\SuratMasukNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuratMasukController extends Controller
{
    /** Role yang boleh menginput/mengelola surat masuk (Admin & TU). */
    private const ROLE_INPUT = ['admin', 'tu'];

    /** Role penerima disposisi awal otomatis, urut berdasarkan prioritas Eselon 1 (Deputi). */
    private const ROLE_PENERIMA_AWAL = ['eselon1', 'eselon2'];

    public function __construct(private SuratMasukNumberService $numbers)
    {
    }

    public function index(Request $request)
    {
        $query = SuratMasuk::query()->with('pembuat');

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

        return view('surat-masuk.index', [
            'suratMasuk' => $suratMasuk,
            'bolehInput' => $this->bolehInput(),
        ]);
    }

    public function create()
    {
        $this->pastikanBolehInput();

        return view('surat-masuk.create', [
            'nomorAgendaBerikutnya' => $this->numbers->nomorBerikutnya(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanBolehInput();

        // nomor_agenda dibuat otomatis. nomor_surat (nomor dari pengirim) bersifat opsional.
        $validated = $request->validate([
            'nomor_surat' => ['nullable', 'string', 'max:150'],
            'asal_surat' => ['required', 'string', 'max:255'],
            'tanggal_surat' => ['required', 'date'],
            'perihal' => ['required', 'string'],
            'drive_link' => ['nullable', 'url'],
        ]);

        $penerima = $this->cariPenerimaAwal();

        $suratMasuk = DB::transaction(function () use ($validated, $penerima) {
            $suratMasuk = SuratMasuk::create([
                'nomor_agenda' => $this->numbers->ambil(),
                'nomor_surat' => filled($validated['nomor_surat'] ?? null) ? $validated['nomor_surat'] : '-',
                'asal_surat' => $validated['asal_surat'],
                'tanggal_surat' => $validated['tanggal_surat'],
                'perihal' => $validated['perihal'],
                'drive_link' => $validated['drive_link'] ?? null,
                'tanggal_diterima' => now()->toDateString(),
                'created_by_id' => Auth::id(),
                'status' => $penerima ? 'didisposisi' : 'baru',
            ]);

            if ($penerima) {
                Disposisi::create([
                    'surat_masuk_id' => $suratMasuk->id,
                    'parent_dispo_id' => null,
                    'pengirim_id' => Auth::id(),
                    'penerima_id' => $penerima->id,
                    'instruksi' => 'Mohon petunjuk dan arahan Ibu Deputi.',
                    'sifat' => 'biasa',
                    'status' => 'baru',
                ]);
            }

            return $suratMasuk;
        });

        if (!$penerima) {
            return redirect()->route('surat-masuk.index')->with(
                'warning',
                'Surat tersimpan dengan nomor agenda ' . $suratMasuk->nomor_agenda
                . ', tetapi belum ada akun Deputi/Pimpinan (eselon1) sehingga belum didisposisikan.'
            );
        }

        return redirect()->route('surat-masuk.index')->with(
            'success',
            'Surat masuk tersimpan dengan nomor agenda ' . $suratMasuk->nomor_agenda . ' dan diteruskan ke Deputi.'
        );
    }

    public function show($id): RedirectResponse
    {
        $suratMasuk = SuratMasuk::with('disposisi')->findOrFail($id);

        $disposisiUtama = $suratMasuk->disposisi->first();
        if ($disposisiUtama) {
            return redirect()->route('disposisi.show', $disposisiUtama->id);
        }

        return redirect()->route('surat-masuk.index')
            ->with('error', 'Disposisi untuk surat ini belum ditemukan.');
    }

    public function destroy($id): RedirectResponse
    {
        $this->pastikanBolehInput();

        $suratMasuk = SuratMasuk::findOrFail($id);

        DB::transaction(function () use ($suratMasuk) {
            // Hapus seluruh rantai disposisi terkait terlebih dahulu
            Disposisi::where('surat_masuk_id', $suratMasuk->id)->delete();
            
            // Hapus data surat masuk utama
            $suratMasuk->delete();
        });

        return redirect()->route('surat-masuk.index')->with(
            'success',
            'Data Surat Masuk beserta seluruh rantai disposisinya berhasil dihapus.'
        );
    }

    private function cariPenerimaAwal(): ?User
    {
        foreach (self::ROLE_PENERIMA_AWAL as $role) {
            $user = User::where('role', $role)->first();
            if ($user) {
                return $user;
            }
        }

        return null;
    }

    private function bolehInput(): bool
    {
        return in_array(Auth::user()?->role, self::ROLE_INPUT, true);
    }

    private function pastikanBolehInput(): void
    {
        abort_unless($this->bolehInput(), 403, 'Hanya admin / TU yang boleh menginput atau mengelola surat masuk.');
    }
}