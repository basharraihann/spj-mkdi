<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisposisiController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Ambil disposisi tempat user login sebagai penerima
        $disposisi = Disposisi::with(['suratMasuk', 'pengirim'])
            ->where('penerima_id', $userId)
            ->latest()
            ->get();

        return view('disposisi.index', compact('disposisi'));
    }

    public function show($id)
    {
        $disposisi = Disposisi::with(['suratMasuk', 'pengirim', 'penerima', 'children.penerima'])->findOrFail($id);

        // Tandai sudah dibaca jika penerima membuka detail
        if ($disposisi->penerima_id === Auth::id() && $disposisi->status === 'baru') {
            $disposisi->update([
                'status'    => 'dibaca',
                'dibaca_at' => now(),
            ]);
        }

        // Ambil daftar pegawai / bawahan untuk form disposisi selanjutnya
        $users = User::where('id', '!=', Auth::id())->get();

        return view('disposisi.show', compact('disposisi', 'users'));
    }

    public function store(Request $request, $id)
    {
        $parentDispo = Disposisi::findOrFail($id);

        $validated = $request->validate([
            'penerima_id'   => 'required|exists:users,id',
            'instruksi'     => 'required|string',
            'tenggat_waktu' => 'nullable|date',
            'sifat'         => 'required|in:biasa,segera,rahasia',
        ]);

        Disposisi::create([
            'surat_masuk_id'  => $parentDispo->surat_masuk_id,
            'parent_dispo_id' => $parentDispo->id,
            'pengirim_id'     => Auth::id(),
            'penerima_id'     => $validated['penerima_id'],
            'instruksi'       => $validated['instruksi'],
            'tenggat_waktu'   => $validated['tenggat_waktu'],
            'sifat'           => $validated['sifat'],
            'status'          => 'baru',
        ]);

        $parentDispo->update(['status' => 'diproses']);

        return redirect()->route('disposisi.show', $id)->with('success', 'Disposisi berhasil diteruskan.');
    }
}