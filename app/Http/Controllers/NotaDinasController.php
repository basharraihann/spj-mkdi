<?php

namespace App\Http\Controllers;

use App\Models\NotaDinas;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaDinasController extends Controller
{
    public function create($disposisi_id)
    {
        $disposisi = Disposisi::with('suratMasuk')->findOrFail($disposisi_id);
        return view('nota-dinas.create', compact('disposisi'));
    }

    public function store(Request $request, $disposisi_id)
    {
        $disposisi = Disposisi::findOrFail($disposisi_id);

        $validated = $request->validate([
            'perihal'          => 'required|string',
            'ringkasan_hasil'  => 'required|string',
            'drive_link_hasil' => 'nullable|url',
        ]);

        $notaDinas = NotaDinas::create([
            'disposisi_id'     => $disposisi->id,
            'nomor_nd'         => 'ND-' . time(),
            'perihal'          => $validated['perihal'],
            'ringkasan_hasil'  => $validated['ringkasan_hasil'],
            'drive_link_hasil' => $validated['drive_link_hasil'],
            'pembuat_id'       => Auth::id(),
        ]);

        $disposisi->update(['status' => 'selesai']);

        return redirect()->route('disposisi.show', $disposisi->id)->with('success', 'Nota Dinas laporan pengerjaan berhasil dikirim.');
    }

    public function show($id)
    {
        $notaDinas = NotaDinas::with('disposisi', 'pembuat')->findOrFail($id);
        return view('nota-dinas.show', compact('notaDinas'));
    }
}