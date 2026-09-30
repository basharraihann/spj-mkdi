<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\KabupatenKota;
use App\Models\NominatifEntry;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class NominatifController extends Controller
{
    /**
     * Daftar nominatif, gabungan dua sumber:
     * - agenda: nominatif perjalanan dinas (PNS / Non PNS), hanya yang ada pesertanya
     * - nominatif_entries: Daftar Honorarium Narasumber yang dibuat mandiri tanpa agenda
     */
    public function index()
    {
        $rows = collect();

        $agendas = Agenda::with('pegawai')->latest()->get();

        foreach ($agendas as $agenda) {
            $komponen = $agenda->komponen_biaya ?? [];

            foreach (['pns' => 'PNS', 'non-pns' => 'Non PNS'] as $slug => $label) {
                $peserta = $agenda->pegawai->where('status_kepegawaian', $label);

                if ($peserta->isEmpty()) {
                    continue;
                }

                $total = $peserta->sum(function ($p) use ($komponen) {
                    return collect($komponen)->sum(fn($key) => (float) ($p->pivot->{$key} ?? 0));
                });

                $tujuan = collect([$agenda->kota_tujuan ?? null, $agenda->tujuan ?? null])->filter()->implode(', ');

                $rows->push([
                    'id' => 'agenda-' . $agenda->id . '-' . $slug,
                    'sumber' => 'agenda',
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'tujuan' => $tujuan ?: '-',
                    'tanggal' => $this->labelTanggal($agenda->tanggal_mulai, $agenda->tanggal_selesai),
                    'sort' => optional($agenda->tanggal_mulai)->timestamp ?? 0,
                    'status' => $label,
                    'jumlah_peserta' => $peserta->count(),
                    'total' => $total,
                    'pdf_url' => route('agendas.nominatif', ['agenda' => $agenda->id, 'status' => $slug]),
                    'edit_url' => null,
                    'delete_url' => null,
                ]);
            }
        }

        foreach (NominatifEntry::with('peserta')->get() as $entry) {
            $tujuan = collect([$entry->kota ?? null, $entry->provinsi ?? null])->filter()->implode(', ');

            $rows->push([
                'id' => 'entry-' . $entry->id,
                'sumber' => 'mandiri',
                'uraian_kegiatan' => $entry->uraian_kegiatan,
                'tujuan' => $tujuan ?: '-',
                'tanggal' => $this->labelTanggal($entry->tanggal, null),
                'sort' => optional($entry->tanggal)->timestamp ?? 0,
                'status' => 'Honorarium',
                'jumlah_peserta' => $entry->peserta->count(),
                'total' => $entry->totalBruto(),
                'pdf_url' => route('nominatif.pdf', $entry->id),
                'edit_url' => route('nominatif.edit', $entry->id),
                'delete_url' => route('nominatif.destroy', $entry->id),
            ]);
        }

        $rows = $rows->sortByDesc('sort')->values();

        return view('nominatif.index', compact('rows'));
    }

    public function create()
    {
        $kabKotaMap = KabupatenKota::orderBy('nama')->get()->groupBy('provinsi')->map(fn($group) => $group->pluck('nama')->values());

        return view('nominatif.create', [
            'nominatif' => null,
            'pegawaiList' => Pegawai::orderBy('nama')->get(),
            'kabKotaMap' => $kabKotaMap,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data) {
            $entry = NominatifEntry::create(Arr::except($data, ['peserta']));
            $this->simpanPeserta($entry, $data['peserta']);
        });

        return redirect()->route('nominatif.index')
            ->with('success', 'Nominatif berhasil dibuat.');
    }

    public function edit(NominatifEntry $nominatif)
    {
        $nominatif->load('peserta');

        $kabKotaMap = KabupatenKota::orderBy('nama')->get()->groupBy('provinsi')->map(fn($group) => $group->pluck('nama')->values());

        return view('nominatif.edit', [
            'nominatif' => $nominatif,
            'pegawaiList' => Pegawai::orderBy('nama')->get(),
            'kabKotaMap' => $kabKotaMap,
        ]);
    }

    public function update(Request $request, NominatifEntry $nominatif)
    {
        $data = $this->validasi($request);

        DB::transaction(function () use ($data, $nominatif) {
            $nominatif->update(Arr::except($data, ['peserta']));
            $this->simpanPeserta($nominatif, $data['peserta']);
        });

        return redirect()->route('nominatif.index')
            ->with('success', 'Nominatif berhasil diperbarui.');
    }

    public function destroy(NominatifEntry $nominatif)
    {
        $nominatif->delete(); // baris narasumber ikut terhapus (cascade)

        return redirect()->route('nominatif.index')
            ->with('success', 'Nominatif berhasil dihapus.');
    }

    /**
     * PDF 2 halaman (landscape):
     * 1. Daftar Honorarium Narasumber + tanda tangan Bendahara dan PPK
     * 2. Daftar Hadir Narasumber
     */
    public function pdf(NominatifEntry $nominatif)
    {
        $nominatif->load(['peserta', 'ppk', 'bendahara']);

        $pdf = Pdf::loadView('pdf.honorarium-narasumber', [
            'entry' => $nominatif,
            'peserta' => $nominatif->peserta,
            'ppk' => $nominatif->ppk,
            'bendahara' => $nominatif->bendahara,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream('Honorarium-Narasumber-' . $nominatif->id . '.pdf');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'uraian_kegiatan' => 'required|string',
            'tanggal' => 'required|date',
            'ppk_id' => 'required|exists:pegawai,id',
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'bendahara_id' => 'required|exists:pegawai,id',
            'peserta' => 'required|array|min:1',
            'peserta.*.nama' => 'required|string|max:255',
            'peserta.*.npwp' => 'nullable|string|max:50',
            'peserta.*.sebagai' => 'nullable|string|max:100',
            'peserta.*.instansi' => 'nullable|string|max:255',
            'peserta.*.golongan' => 'nullable|string|max:50',
            'peserta.*.jabatan' => 'required|string|max:255',
            'peserta.*.honor' => 'required|string',
            'peserta.*.oj' => 'required|integer|min:1',
            'peserta.*.pajak_persen' => 'nullable|numeric|min:0|max:100',
        ], [
            'peserta.required' => 'Tambahkan minimal satu narasumber.',
            'peserta.*.nama.required' => 'Nama narasumber wajib diisi.',
            'peserta.*.jabatan.required' => 'Jabatan narasumber wajib diisi.',
            'peserta.*.honor.required' => 'Honor narasumber wajib diisi.',
            'peserta.*.oj.required' => 'Jumlah OJ wajib diisi.',
            'peserta.*.oj.min' => 'Jumlah OJ minimal 1.',
        ]);
    }

    private function simpanPeserta(NominatifEntry $entry, array $peserta): void
    {
        $entry->peserta()->delete();

        foreach (array_values($peserta) as $i => $row) {
            $entry->peserta()->create([
                'urutan' => $i,
                'nama' => $row['nama'],
                'npwp' => $row['npwp'] ?? null,
                'sebagai' => ($row['sebagai'] ?? '') !== '' ? $row['sebagai'] : 'Narasumber',
                'instansi' => $row['instansi'] ?? null,
                'golongan' => $row['golongan'] ?? null,
                'jabatan' => $row['jabatan'] ?? null,
                // Honor dikirim dengan titik ribuan ("1.000.000"), ambil angkanya saja
                'honor' => (int) preg_replace('/\D/', '', (string) $row['honor']),
                'oj' => (int) $row['oj'],
                'pajak_persen' => (float) ($row['pajak_persen'] ?? 0),
            ]);
        }
    }

    private function labelTanggal($mulai, $selesai): string
    {
        if (!$mulai) {
            return '-';
        }

        $awal = $mulai->translatedFormat('d M Y');

        if (!$selesai || $mulai->isSameDay($selesai)) {
            return $awal;
        }

        return $awal . ' – ' . $selesai->translatedFormat('d M Y');
    }
}