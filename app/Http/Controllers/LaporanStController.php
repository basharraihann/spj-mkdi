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

        // Form hanya menerima upload berkas PDF.
        $data = $request->validate([
            'berkas' => [$kirim ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:10240'], // PDF, maks. 10 MB (disesuaikan dengan tampilan UI)
        ], [
            'berkas.required' => 'Berkas laporan wajib diunggah sebelum dikirim.',
            'berkas.mimes'    => 'Berkas laporan harus berformat PDF.',
            'berkas.max'      => 'Ukuran berkas laporan maksimal 10 MB.',
            'berkas.uploaded' => 'Berkas gagal diunggah. Pastikan ukurannya tidak lebih dari 10 MB.',
        ]);

        try {
            $this->laporan->simpan(
                $suratTugas,
                $request->user(),
                [], // Tidak ada input teks (notulensi/materi) yang dikirim
                $kirim,
                $request->file('berkas')
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