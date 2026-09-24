<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = now();

        // Statistik jumlah agenda
        $agendaBulanIni = Agenda::whereMonth('tanggal_mulai', $now->month)
            ->whereYear('tanggal_mulai', $now->year)
            ->count();

        $agendaTahunIni = Agenda::whereYear('tanggal_mulai', $now->year)
            ->count();

        // Total dana diajukan (LS) bulan ini — dihitung dari rincian biaya
        // tiap peserta (pivot agenda_pegawai), pakai accessor totalBiaya di model Agenda.
        $totalDanaBulanIni = Agenda::whereMonth('tanggal_mulai', $now->month)
            ->whereYear('tanggal_mulai', $now->year)
            ->with('pegawai')
            ->get()
            ->sum(fn($agenda) => $agenda->totalBiaya);

        // Data chart per bulan (Jan - Des) tahun berjalan
        $chartLabels = [];
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = Carbon::create()->month($i)->translatedFormat('M');
            $chartData[] = Agenda::whereMonth('tanggal_mulai', $i)
                ->whereYear('tanggal_mulai', $now->year)
                ->with('pegawai')
                ->get()
                ->sum(fn($agenda) => $agenda->totalBiaya);
        }

        // Data awal kalender (bulan dari ?bulan=YYYY-MM, default bulan berjalan)
        $kalender = $this->dataKalender($request);

        return view('dashboard', compact(
            'agendaBulanIni',
            'agendaTahunIni',
            'totalDanaBulanIni',
            'chartLabels',
            'chartData',
            'kalender'
        ));
    }

    /**
     * Endpoint JSON untuk pindah bulan kalender tanpa reload halaman.
     * GET /dashboard/kalender?bulan=2026-10
     */
    public function kalender(Request $request)
    {
        return response()->json($this->dataKalender($request));
    }

    private function dataKalender(Request $request): array
    {
        $now = now();
        $bulanParam = (string) $request->query('bulan', '');

        // Validasi format YYYY-MM; kalau salah/kosong, pakai bulan berjalan
        $bulan = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanParam)
            ? Carbon::createFromFormat('!Y-m', $bulanParam)
            : $now->copy()->startOfMonth();

        $startOfMonth = $bulan->copy()->startOfMonth();
        $endOfMonth = $bulan->copy()->endOfMonth();

        // Agenda yang periodenya overlap dengan bulan ini (termasuk yang
        // nyambung dari bulan lalu / ke bulan depan).
        $agendaList = Agenda::where('tanggal_mulai', '<=', $endOfMonth)
            ->where('tanggal_selesai', '>=', $startOfMonth)
            ->with('pegawai')
            ->orderBy('tanggal_mulai')
            ->get();

        // marks[tanggal] = [ {id, uraian, lokasi, pegawai[], periode}, ... ]
        $marks = [];
        foreach ($agendaList as $agenda) {
            $periodeMulai = $agenda->tanggal_mulai->greaterThan($startOfMonth)
                ? $agenda->tanggal_mulai
                : $startOfMonth;
            $periodeSelesai = $agenda->tanggal_selesai->lessThan($endOfMonth)
                ? $agenda->tanggal_selesai
                : $endOfMonth;

            $item = [
                'id' => $agenda->id,
                'uraian' => $agenda->uraian_kegiatan,
                'lokasi' => collect([$agenda->kota, $agenda->provinsi])->filter()->implode(', ')
                    ?: $agenda->tujuan,
                'pegawai' => $agenda->pegawai->pluck('nama')->values()->all(),
                'periode' => $agenda->tanggal_mulai->translatedFormat('d M')
                    . ($agenda->tanggal_mulai->isSameDay($agenda->tanggal_selesai)
                        ? ''
                        : ' – ' . $agenda->tanggal_selesai->translatedFormat('d M Y')),
            ];

            for ($d = $periodeMulai->copy(); $d->lte($periodeSelesai); $d->addDay()) {
                $marks[$d->day][] = $item;
            }
        }

        $isBulanIni = $startOfMonth->isSameMonth($now);

        return [
            'value' => $startOfMonth->format('Y-m'),
            'tahun' => $startOfMonth->year,
            'bulan' => $startOfMonth->month,
            'namaBulan' => $startOfMonth->translatedFormat('F Y'),
            'offset' => $startOfMonth->dayOfWeekIso - 1, // Senin = 0
            'jumlahHari' => $startOfMonth->daysInMonth,
            'hariIni' => $isBulanIni ? $now->day : null,
            'isBulanIni' => $isBulanIni,
            'prev' => $startOfMonth->copy()->subMonth()->format('Y-m'),
            'next' => $startOfMonth->copy()->addMonth()->format('Y-m'),
            'marks' => $marks,
        ];
    }
}