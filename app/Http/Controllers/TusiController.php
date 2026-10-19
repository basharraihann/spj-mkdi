<?php

namespace App\Http\Controllers;

use App\Models\Tusi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TusiController extends Controller
{
    public function index()
    {
        $this->pastikanAdmin();

        return view('tusi.index', [
            'items' => Tusi::withCount('suratTugas')->urut()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->pastikanAdmin();
        Tusi::create($this->validasi($request));

        return back()->with('success', 'Tusi ditambahkan.');
    }

    public function update(Request $request, Tusi $tusi): RedirectResponse
    {
        $this->pastikanAdmin();
        $tusi->update($this->validasi($request, $tusi));

        return back()->with('success', 'Tusi diperbarui.');
    }

    public function destroy(Tusi $tusi): RedirectResponse
    {
        $this->pastikanAdmin();

        if ($tusi->suratTugas()->exists()) {
            return back()->with('error', 'Tusi masih dipakai ST. Nonaktifkan saja, jangan dihapus.');
        }
        $tusi->delete();

        return back()->with('success', 'Tusi dihapus.');
    }

    private function validasi(Request $request, ?Tusi $tusi = null): array
    {
        $v = $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('tusi', 'kode')->ignore($tusi?->id)],
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'urutan' => ['nullable', 'integer', 'min:0', 'max:65000'],
        ]);

        return $v + ['urutan' => $v['urutan'] ?? 0, 'aktif' => $request->boolean('aktif')];
    }

    private function pastikanAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }
}
