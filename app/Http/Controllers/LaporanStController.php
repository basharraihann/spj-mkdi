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
            'materi'    => ['nullable', 'array'],
            'materi.*'  => ['file', 'mimes:pdf,ppt,pptx', 'max:10240'], // Maks 10 MB per file
        ], [
            'notulensi.required' => 'Berkas notulensi / laporan wajib diunggah sebelum dikirim.',
            'notulensi.mimes'    => 'Berkas notulensi harus berformat PDF.',
            'notulensi.max'      => 'Ukuran berkas notulensi maksimal 5 MB.',
            'notulensi.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 5 MB.',
            'materi.*.mimes'     => 'Format file materi harus PDF, PPT, atau PPTX.',
            'materi.*.max'       => 'Ukuran masing-masing file materi maksimal 10 MB.',
        ]);

        // Kumpulkan semua file materi yang diunggah
        $materiFiles = [];
        if ($request->hasFile('materi')) {
            $files = $request->file('materi');
            $materiFiles = is_array($files) ? $files : [$files];
        }

        try {
            $this->laporan->simpan(
                $suratTugas,
                $request->user(),
                [], 
                $kirim,
                $request->file('notulensi'), // File utama Notulensi
                $materiFiles                 // Array File Materi
            );
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('surat-tugas.show', $suratTugas)->with(
            'success',
            $kirim ? 'Laporan dikirim. ST ditandai selesai.' : 'Draf laporan disimpan.'
        );
    }

    /** Buka atau unduh berkas PDF Notulensi. */
    public function berkas(Request $request, SuratTugas $suratTugas)
    {
        abort_unless($this->laporan->bolehUnduh($suratTugas, $request->user()), 403);

        $laporan = LaporanSt::where('surat_tugas_id', $suratTugas->id)->firstOrFail();
        $path = $laporan->file_laporan_path;

        abort_unless($path && Storage::disk(LaporanStService::DISK)->exists($path), 404);

        $filename = $laporan->file_laporan_nama ?: 'notulensi.pdf';

        if ($request->boolean('download')) {
            return Storage::disk(LaporanStService::DISK)->download($path, $filename);
        }

        return Storage::disk(LaporanStService::DISK)->response(
            $path,
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }

    /** Buka atau unduh berkas Materi tertentu berdasarkan nama file. */
    public function berkasMateri(Request $request, SuratTugas $suratTugas, string $filename)
    {
        abort_unless($this->laporan->bolehUnduh($suratTugas, $request->user()), 403);

        $path = 'laporan-st/' . $suratTugas->id . '/materi/' . $filename;

        abort_unless(Storage::disk(LaporanStService::DISK)->exists($path), 404);

        if ($request->boolean('download')) {
            return Storage::disk(LaporanStService::DISK)->download($path, $filename);
        }

        return Storage::disk(LaporanStService::DISK)->response($path, $filename);
    }
}