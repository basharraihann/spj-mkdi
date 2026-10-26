<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class DocumentPdfController extends Controller
{
    /**
     * Cetak Lembar Disposisi Digital.
     */
    public function cetakDisposisi($id): Response
    {
        $disposisi = Disposisi::with(['suratMasuk', 'pengirim', 'penerima'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.lembar-disposisi', compact('disposisi'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Lembar-Disposisi-' . ($disposisi->suratMasuk->nomor_agenda ?? $disposisi->id) . '.pdf');
    }

    /**
     * Cetak/Stream PDF Dokumen Keluar Berdasarkan Template Resmi.
     */
    public function cetakSuratKeluar(SuratKeluar $suratKeluar): Response
    {
        $viewName = match ($suratKeluar->jenis_dokumen) {
            'nota_dinas' => 'pdf.nota-dinas',
            'undangan' => 'pdf.undangan-eksternal',
            'telaah_staf' => 'pdf.telaah-staf',
            'notula' => 'pdf.notula',
            'surat_pernyataan' => 'pdf.surat-pernyataan',
            'laporan' => 'pdf.laporan',
            default => abort(404, 'Template dokumen tidak ditemukan.'),
        };

        $pdf = Pdf::loadView($viewName, ['dokumen' => $suratKeluar])
            ->setPaper('a4', 'portrait');

        return $pdf->stream($suratKeluar->nama_jenis . '-' . ($suratKeluar->nomor_surat ?: 'Draft') . '.pdf');
    }
}