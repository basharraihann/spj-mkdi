<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuratKeluarController extends Controller
{
    /**
     * Tampilkan daftar monitoring Surat Keluar & Draf Dokumen.
     */
    public function index(Request $request): View
    {
        $query = SuratKeluar::with('pembuat');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hal', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_dokumen', $request->jenis);
        }

        $suratKeluar = $query->latest()->paginate(10)->withQueryString();

        return view('surat-keluar.index', compact('suratKeluar'));
    }

    /**
     * Form pembuatan draf Surat Keluar / Dokumen baru.
     */
    public function create(): View
    {
        return view('surat-keluar.create');
    }

    /**
     * Simpan draf Surat Keluar / Dokumen ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'in:nota_dinas,undangan,telaah_staf,notula,surat_pernyataan'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'sifat' => ['required', 'in:biasa,terbatas,rahasia'],
            'hal' => ['required', 'string', 'max:255'],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['nullable', 'string', 'max:255'],
            'dari' => ['nullable', 'string', 'max:255'],
            'isi_payload' => ['required', 'array'],
            'tembusan' => ['nullable', 'array'],
        ]);

        $suratKeluar = SuratKeluar::create([
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'nomor_surat' => $validated['nomor_surat'] ?? null,
            'sifat' => $validated['sifat'],
            'hal' => $validated['hal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tujuan' => $validated['tujuan'] ?? null,
            'dari' => $validated['dari'] ?? null,
            'isi_payload' => $validated['isi_payload'],
            'tembusan' => $validated['tembusan'] ?? [],
            'created_by' => Auth::id(),
            'status_persetujuan' => 'draft',
        ]);

        return redirect()->route('surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Draf ' . $suratKeluar->nama_jenis . ' berhasil dibuat.');
    }

    /**
     * Tampilkan detail & pratinjau Surat Keluar.
     */
    public function show(SuratKeluar $suratKeluar): View
    {
        $suratKeluar->load('pembuat');

        return view('surat-keluar.show', compact('suratKeluar'));
    }

    /**
     * Form edit Surat Keluar / Dokumen.
     */
    public function edit(SuratKeluar $suratKeluar): View
    {
        return view('surat-keluar.edit', compact('suratKeluar'));
    }

    /**
     * Update data Surat Keluar.
     */
    public function update(Request $request, SuratKeluar $suratKeluar): RedirectResponse
    {
        $validated = $request->validate([
            'jenis_dokumen' => ['required', 'in:nota_dinas,undangan,telaah_staf,notula,surat_pernyataan'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'sifat' => ['required', 'in:biasa,terbatas,rahasia'],
            'hal' => ['required', 'string', 'max:255'],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['nullable', 'string', 'max:255'],
            'dari' => ['nullable', 'string', 'max:255'],
            'isi_payload' => ['required', 'array'],
            'tembusan' => ['nullable', 'array'],
        ]);

        $suratKeluar->update([
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'nomor_surat' => $validated['nomor_surat'] ?? $suratKeluar->nomor_surat,
            'sifat' => $validated['sifat'],
            'hal' => $validated['hal'],
            'tanggal_surat' => $validated['tanggal_surat'],
            'tujuan' => $validated['tujuan'] ?? null,
            'dari' => $validated['dari'] ?? null,
            'isi_payload' => $validated['isi_payload'],
            'tembusan' => $validated['tembusan'] ?? [],
        ]);

        return redirect()->route('surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Data ' . $suratKeluar->nama_jenis . ' berhasil diperbarui.');
    }

    /**
     * Hapus data Surat Keluar.
     */
    public function destroy(SuratKeluar $suratKeluar): RedirectResponse
    {
        $namaJenis = $suratKeluar->nama_jenis;
        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Dokumen ' . $namaJenis . ' berhasil dihapus.');
    }
}