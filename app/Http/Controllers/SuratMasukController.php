<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratMasukController extends Controller
{
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

        $suratMasuk = $query->latest()->paginate(10);

        return view('surat-masuk.index', compact('suratMasuk'));
    }

    public function create()
    {
        return view('surat-masuk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_agenda'   => 'required|string|unique:surat_masuk,nomor_agenda',
            'nomor_surat'    => 'required|string',
            'asal_surat'     => 'required|string',
            'tanggal_surat'  => 'required|date',
            'perihal'        => 'required|string',
            'drive_link'     => 'nullable|url',
        ]);

        $validated['tanggal_diterima'] = now()->toDateString();
        $validated['created_by_id']    = Auth::id();
        $validated['status']           = 'didisposisi';

        $suratMasuk = SuratMasuk::create($validated);

        // Cari user dengan role 'deputi' untuk menerima disposisi awal
        $deputiUser = User::where('role', 'deputi')->first() ?? User::where('id', '!=', Auth::id())->first();

        if ($deputiUser) {
            Disposisi::create([
                'surat_masuk_id' => $suratMasuk->id,
                'parent_dispo_id'=> null,
                'pengirim_id'    => Auth::id(),
                'penerima_id'    => $deputiUser->id,
                'instruksi'      => 'Mohon petunjuk dan arahan Ibu Deputi.',
                'sifat'          => 'biasa',
                'status'         => 'baru',
            ]);
        }

        return redirect()->route('surat-masuk.index')->with('success', 'Surat Masuk berhasil disimpan dan diteruskan ke Deputi.');
    }

    public function show($id)
    {
        $suratMasuk = SuratMasuk::with('disposisi.pengirim', 'disposisi.penerima')->findOrFail($id);
        
        // Redirect ke tampilan detail disposisi pertama
        $disposisiUtama = $suratMasuk->disposisi->first();
        if ($disposisiUtama) {
            return redirect()->route('disposisi.show', $disposisiUtama->id);
        }

        return view('surat-masuk.index')->with('error', 'Disposisi untuk surat ini belum ditemukan.');
    }
}