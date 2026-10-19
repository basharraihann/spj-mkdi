<?php

namespace App\Http\Controllers;

use App\Models\LaporanStTugas;
use Illuminate\Http\Request;

class TugasSayaController extends Controller
{
    /**
     * Daftar tugas "Isi Laporan" milik akun yang sedang login.
     * Tugas dibuat saat ST diterbitkan (LaporanStService::buatTugas).
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // Tab yang tersedia. Default: tugas yang masih menunggu.
        $tabValid = [
            LaporanStTugas::STATUS_MENUNGGU,
            LaporanStTugas::STATUS_SELESAI,
            LaporanStTugas::STATUS_DITUTUP,
            'semua',
        ];
        $status = $request->query('status', LaporanStTugas::STATUS_MENUNGGU);
        if (!in_array($status, $tabValid, true)) {
            $status = LaporanStTugas::STATUS_MENUNGGU;
        }

        // Dasar query: tugas milik akun ini, ST yang dibatalkan tidak ikut dihitung
        $dasar = LaporanStTugas::where('user_id', $userId)
            ->whereHas('suratTugas', fn($q) => $q->where('status', '!=', 'batal'));

        // Angka di tiap tab
        $jumlah = [
            'menunggu' => (clone $dasar)->where('status', LaporanStTugas::STATUS_MENUNGGU)->count(),
            'selesai' => (clone $dasar)->where('status', LaporanStTugas::STATUS_SELESAI)->count(),
            'ditutup' => (clone $dasar)->where('status', LaporanStTugas::STATUS_DITUTUP)->count(),
            'semua' => (clone $dasar)->count(),
        ];

        $tugas = (clone $dasar)
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->with(['suratTugas.laporan', 'suratTugas.tusi'])
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('tugas-saya.index', compact('tugas', 'status', 'jumlah'));
    }
}
