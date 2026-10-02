<?php

namespace App\Http\Controllers;

use App\Models\NominatifEntry;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NominatifCustomController extends Controller
{
    public function create()
    {
        return view('nominatif.custom.custom-form', [
            'nominatif' => null,
            'pegawaiList' => Pegawai::orderBy('nama')->get(),
            'action' => route('nominatif.custom.store'),
            'method' => 'POST',
            'submitLabel' => 'Simpan Nominatif',
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $entry = NominatifEntry::create(
                Arr::except($data, ['items']) + ['jenis_detail' => 'custom']
            );
            $this->simpanItems($entry, $data['items']);
        });

        return redirect()->route('nominatif.index')->with('success', 'Nominatif berhasil dibuat.');
    }

    public function edit(NominatifEntry $nominatif)
    {
        abort_unless($nominatif->jenis_detail === 'custom', 404);
        $nominatif->load('items');

        return view('nominatif.custom.custom-form', [
            'nominatif' => $nominatif,
            'pegawaiList' => Pegawai::orderBy('nama')->get(),
            'action' => route('nominatif.custom.update', $nominatif),
            'method' => 'PUT',
            'submitLabel' => 'Simpan Perubahan',
        ]);
    }

    public function update(Request $request, NominatifEntry $nominatif)
    {
        abort_unless($nominatif->jenis_detail === 'custom', 404);
        $data = $this->validasi($request);

        DB::transaction(function () use ($data, $nominatif) {
            $nominatif->update(Arr::except($data, ['items']));
            $this->simpanItems($nominatif, $data['items']);
        });

        return redirect()->route('nominatif.index')->with('success', 'Nominatif berhasil diperbarui.');
    }

    public function pdf(NominatifEntry $nominatif)
    {
        abort_unless($nominatif->jenis_detail === 'custom', 404);
        $nominatif->load(['items', 'ppk', 'bendahara', 'penanggungJawab']);

        return Pdf::loadView('pdf.nominatif-custom', ['entry' => $nominatif])
            ->setPaper('a4', 'landscape')
            ->stream('Nominatif-Custom-' . $nominatif->id . '.pdf');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'uraian_kegiatan' => 'required|string',
            'tanggal' => 'required|date',
            'ppk_id' => 'required|exists:pegawai,id',
            'bendahara_id' => 'required|exists:pegawai,id',
            'penanggung_jawab_id' => 'required|exists:pegawai,id',
            'items' => 'required|array|min:1',
            'items.*.uraian' => 'required|string',
            'items.*.harga' => 'required|string',
            'items.*.jumlah' => 'required|numeric|min:0.01',
            'items.*.satuan' => 'nullable|string|max:50',
            'items.*.ppn_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.pph22_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.pph23_persen' => 'nullable|numeric|min:0|max:100',
        ], [
            'items.required' => 'Tambahkan minimal satu item.',
            'items.*.uraian.required' => 'Uraian item wajib diisi.',
            'items.*.harga.required' => 'Harga item wajib diisi.',
            'items.*.jumlah.required' => 'Jumlah item wajib diisi.',
        ]);
    }

    private function simpanItems(NominatifEntry $entry, array $items): void
    {
        $entry->items()->delete();

        foreach (array_values($items) as $i => $row) {
            $entry->items()->create([
                'urutan' => $i,
                'uraian' => $row['uraian'],
                'harga' => (int) preg_replace('/\D/', '', (string) $row['harga']),
                'jumlah' => (float) $row['jumlah'],
                'satuan' => $row['satuan'] ?? null,
                'ppn_persen' => (float) ($row['ppn_persen'] ?? 0),
                'pph22_persen' => (float) ($row['pph22_persen'] ?? 0),
                'pph23_persen' => (float) ($row['pph23_persen'] ?? 0),
            ]);
        }
    }
}