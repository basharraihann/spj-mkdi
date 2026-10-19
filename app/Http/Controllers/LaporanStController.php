<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Services\LaporanStService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $data = $request->validate([
            // notulensi wajib hanya saat dikirim; draf boleh belum lengkap
            'notulensi' => [$kirim ? 'required' : 'nullable', 'string', 'max:50000'],
            'keputusan' => ['nullable', 'string', 'max:20000'],
            'tindak_lanjut' => ['nullable', 'array', 'max:30'],
            'tindak_lanjut.*.uraian' => ['nullable', 'string', 'max:1000'],
            'tindak_lanjut.*.pic' => ['nullable', 'string', 'max:150'],
            'tindak_lanjut.*.tenggat' => ['nullable', 'date'],
        ], [
            'notulensi.required' => 'Notulensi / hasil rapat wajib diisi sebelum laporan dikirim.',
        ]);

        try {
            $this->laporan->simpan($suratTugas, $request->user(), $data, $kirim);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('surat-tugas.show', $suratTugas)->with(
            'success',
            $kirim ? 'Laporan dikirim. ST ditandai selesai.' : 'Draf laporan disimpan.'
        );
    }
}
