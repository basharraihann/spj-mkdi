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

        // Validasi input file Notulensi & Materi
        $data = $request->validate([
            'notulensi' => [$kirim ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:5120'], // PDF, maks 5 MB
            'materi'    => ['nullable'],
            'materi.*'  => ['file', 'mimes:pdf,ppt,pptx', 'max:10240'], // Maks 10 MB per file
        ], [
            'notulensi.required' => 'Berkas notulensi / laporan wajib diunggah sebelum dikirim.',
            'notulensi.mimes'    => 'Berkas notulensi harus berformat PDF.',
            'notulensi.max'      => 'Ukuran berkas notulensi maksimal 5 MB.',
            'notulensi.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 5 MB.',
            'materi.*.mimes'     => 'Format file materi harus PDF, PPT, atau PPTX.',
            'materi.*.max'       => 'Ukuran masing-masing file materi maksimal 10 MB.',
        ]);

        // Kumpulkan file materi jika diunggah
        $materiFile = null;
        if ($request->hasFile('materi')) {
            $files = $request->file('materi');
            $materiFile = is_array($files) ? ($files[0] ?? null) : $files;
        }

        try {
            $this->laporan->simpan(
                $suratTugas,
                $request->user(),
                [], 
                $kirim,
                $request->file('notulensi'), // File utama Notulensi
                $materiFile                 // File Materi
            );
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('surat-tugas.show', $suratTugas)->with(
            'success',
            $kirim ? 'Laporan dikirim. ST ditandai selesai.' : 'Draf laporan disimpan.'
        );
    }

    /** Buka berkas PDF laporan (disimpan privat). */
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

    /** Buka berkas Materi (tanpa butuh migration baru). */
    public function berkasMateri(Request $request, SuratTugas $suratTugas)
    {
        abort_unless($this->laporan->bolehUnduh($suratTugas, $request->user()), 403);

        // Cari file materi di folder storage st
        $files = Storage::disk(LaporanStService::DISK)->files('laporan-st/' . $suratTugas->id . '/materi');
        
        abort_unless(!empty($files), 404);

        $path = $files[0];
        $filename = basename($path);

        return Storage::disk(LaporanStService::DISK)->response(
            $path,
            $filename
        );
    }
}