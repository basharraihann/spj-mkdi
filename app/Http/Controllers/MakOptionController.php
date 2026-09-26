<?php

namespace App\Http\Controllers;

use App\Models\MakOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MakOptionController extends Controller
{
    public function index(Request $request)
    {
        $makOptions = MakOption::orderBy('mak')->get();

        // Kelompokkan per prefix (semua segmen kode MAK kecuali kode belanja di akhir)
        $grouped = $makOptions->groupBy(fn($item) => Str::beforeLast($item->mak, '.'));

        return view('mak-options.index', [
            'grouped' => $grouped,
            'total' => $makOptions->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validated($request);
        MakOption::create($validated);

        return back()->with('success', 'Nomor MAK berhasil ditambahkan.');
    }

    public function update(Request $request, MakOption $makOption)
    {
        $validated = $this->validated($request);
        $makOption->update($validated);

        return back()->with('success', 'Nomor MAK berhasil diperbarui.');
    }

    public function destroy(MakOption $makOption)
    {
        $makOption->delete();

        return back()->with('success', 'Nomor MAK berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'mak' => 'required|string|max:255',
            'uraian_giat' => 'nullable|string|max:255',
            'uraian_komponen' => 'nullable|string|max:255',
            'uraian_akun_ap' => 'nullable|string|max:255',
            'uraian_belanja' => 'nullable|string|max:255',
        ]);
    }
}