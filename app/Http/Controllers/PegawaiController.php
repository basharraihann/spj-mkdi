<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $pegawais = Pegawai::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('nama', 'like', '%' . $request->q . '%')
                        ->orWhere('nip', 'like', '%' . $request->q . '%');
                });
            })
            ->when($request->filled('jabatan'), function ($query) use ($request) {
                $query->where('jabatan', 'like', '%' . $request->jabatan . '%');
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status_kepegawaian', $request->status);
            })
            ->get();

        $sortGolongan = function ($a, $b) {
            $urutanA = $a->urutan ?? PHP_INT_MAX;
            $urutanB = $b->urutan ?? PHP_INT_MAX;

            return $urutanA <=> $urutanB ?: $b->golongan_rank <=> $a->golongan_rank;
        };

        $sortNama = function ($a, $b) {
            $urutanA = $a->urutan ?? PHP_INT_MAX;
            $urutanB = $b->urutan ?? PHP_INT_MAX;

            return $urutanA <=> $urutanB ?: $a->nama <=> $b->nama;
        };

        // Kelompokkan per unit kerja dulu. Yang belum diisi unit_kerja-nya
        // dikumpulkan jadi satu grup 'Belum Diisi Unit' dan selalu ditaruh
        // paling bawah (sortBy pakai prefix 'zzz_' biar kealfabet-an tetap jalan).
        $unitGroups = $pegawais
            ->groupBy(fn($p) => $p->unit_kerja ?: 'Belum Diisi Unit')
            ->sortBy(fn($items, $unit) => $unit === 'Belum Diisi Unit' ? 'zzz_' . $unit : $unit)
            ->map(function ($items, $unit) use ($sortGolongan, $sortNama) {
                return [
                    'unit' => $unit,
                    'pnsList' => $items->where('status_kepegawaian', 'PNS')->sort($sortGolongan)->values(),
                    'nonPnsList' => $items->where('status_kepegawaian', 'Non PNS')->sort($sortNama)->values(),
                    'belumDiisiList' => $items->whereNotIn('status_kepegawaian', ['PNS', 'Non PNS'])->sort($sortNama)->values(),
                    'total' => $items->count(),
                ];
            })
            ->values();

        $totalPegawai = $pegawais->count();

        return view('pegawais.index', compact('unitGroups', 'totalPegawai'));
    }

    /**
     * Simpan urutan manual hasil drag & drop. Menerima array ID pegawai
     * sesuai urutan barunya (dari satu section/grup yang sama).
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:pegawai,id'],
        ]);

        foreach ($validated['ids'] as $index => $id) {
            Pegawai::where('id', $id)->update(['urutan' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Daftar unit kerja unik yang sudah pernah diisi, buat saran di datalist.
     */
    private function unitKerjaList()
    {
        return Pegawai::query()
            ->whereNotNull('unit_kerja')
            ->where('unit_kerja', '!=', '')
            ->distinct()
            ->orderBy('unit_kerja')
            ->pluck('unit_kerja');
    }

    public function create()
    {
        $unitKerjaList = $this->unitKerjaList();

        return view('pegawais.create', compact('unitKerjaList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nama_gelar' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'pangkat' => ['nullable', 'string', 'max:100'],
            'golongan' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'max:255'],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'status_kepegawaian' => ['nullable', 'in:PNS,Non PNS'],
            'role_penandatangan' => ['nullable', 'string', 'max:100'],
        ]);

        Pegawai::create($validated);

        return redirect()->route('pegawais.index')->with('success', 'Pegawai baru berhasil ditambahkan.');
    }

    public function show(Pegawai $pegawai)
    {
        return view('pegawais.show', compact('pegawai'));
    }

    public function edit(Pegawai $pegawai)
    {
        $unitKerjaList = $this->unitKerjaList();

        return view('pegawais.edit', compact('pegawai', 'unitKerjaList'));
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'nama_gelar' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'pangkat' => ['nullable', 'string', 'max:100'],
            'golongan' => ['nullable', 'string', 'max:20'],
            'jabatan' => ['required', 'string', 'max:255'],
            'unit_kerja' => ['required', 'string', 'max:255'],
            'status_kepegawaian' => ['nullable', 'in:PNS,Non PNS'],
            'role_penandatangan' => ['nullable', 'string', 'max:100'],
        ]);

        $pegawai->update($validated);

        return redirect()->route('pegawais.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        return redirect()->route('pegawais.index')->with('success', 'Pegawai berhasil dihapus.');
    }
}