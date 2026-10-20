<?php

namespace App\Http\Controllers;

use App\Models\LaporanSt;
use App\Models\SuratTugas;
use App\Services\LaporanStService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanStController extends Controller
{
    public function __construct(private LaporanStService $laporan)
    {
    }

    /** Simpan draf (aksi=draf) atau kirim laporan (aksi=kirim). */
    public function simpan(Request $request, SuratTugas $suratTugas): RedirectResponse
    {
        abort_unless($this->laporan->bolehIsi($suratTugas, $request->user()), 403);

        $kirim = $request->input('aksi') === 'kirim';

        // Validasi unggah berkas PDF (Maksimal 2 file, masing-masing maks 5 MB)
        $data = $request->validate([
            'berkas'   => [$kirim ? 'required' : 'nullable', 'array', 'max:2'],
            'berkas.*' => ['file', 'mimes:pdf', 'max:5120'], // Maks. 5 MB per file
        ], [
            'berkas.required' => 'Berkas laporan wajib diunggah sebelum dikirim.',
            'berkas.max'      => 'Maksimal file yang diunggah adalah 2 berkas.',
            'berkas.*.mimes'  => 'Setiap berkas laporan harus berformat PDF.',
            'berkas.*.max'    => 'Ukuran masing-masing berkas tidak boleh lebih dari 5 MB.',
        ]);

        // Mengambil file pertama jika ada (atau sesuaikan penanganan array file pada Service Anda)
        $fileBerkas = $request->file('berkas') ? $request->file('berkas')[0] : null;

        try {
            $this->laporan->simpan(
                $suratTugas,
                $request->user(),
                [], // Array data teks kosong karena notulensi & materi tidak lagi diinput lewat form
                $kirim,
                $fileBerkas
            );
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('surat-tugas.show', $suratTugas)->with(
            'success',
            $kirim ? 'Laporan dikirim. ST ditandai selesai.' : 'Draf laporan disimpan.'
        );
    }

    /** Buka berkas PDF laporan (disimpan privat, hanya lewat route ini). */
    public function berkas(Request $request, SuratTugas $suratTugas)
    {
        abort_unless($this->laporan->bolehUnduh($suratTugas, $request->user()), 403);

        $laporan = LaporanSt::where('surat_tugas_id', $suratTugas->id)->firstOrFail();
        $path = $laporan->file_laporan_path;

        abort_unless($path && Storage::disk(LaporanStService::DISK)->exists($path), 404);

        return Storage::disk(LaporanStService::DISK)->response(
            $path,
            $laporan->file_laporan_nama ?: 'laporan.pdf',
            ['Content-Type' => 'application/pdf']
        );
    }
}