<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // KUNCI: Pengalihan khusus untuk Deputi 2, Asdep 2, dan TU Deputi 2 ke Surat Masuk
        if ($user && in_array($user->role, ['deputi-d2', 'asdep-d2', 'tu-d2'], true)) {
            return redirect()->route('surat-masuk.index');
        }

        $now = now();

        // Statistik jumlah agenda
        $agendaBulanIni = Agenda::whereMonth('tanggal_mulai', $now->month)
            ->whereYear('tanggal_mulai', $now->year)
            ->count();

        $danaLsBulanIni = $this->danaLsBulan($now->month, $now->year);
        $totalDanaBulanIni = $danaLsBulanIni['total'];

        $chartLabels = [];
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = Carbon::create()->month($i)->translatedFormat('M');
            $chartData[] = $this->danaLsBulan($i, $now->year)['total'];
        }

        $kalender = $this->dataKalender($request);

        return view('dashboard', compact(
            'agendaBulanIni',
            'totalDanaBulanIni',
            'danaLsBulanIni',
            'chartLabels',
            'chartData',
            'kalender'
        ));
    }

    public function kalender(Request $request)
    {
        return response()->json($this->dataKalender($request));
    }

    private function danaLsBulan(int $bulan, int $tahun): array
    {
        $agendas = Agenda::whereMonth('tanggal_mulai', $bulan)
            ->whereYear('tanggal_mulai', $tahun)
            ->get();

        $totalPerdin = $agendas->sum(function ($agenda) {
            $total = 0;

            if (!empty($agenda->nomor_memo_pns)) {
                $total += $agenda->biaya_asn;
            }

            if (!empty($agenda->nomor_memo_non_pns)) {
                $total += $agenda->biaya_non_asn;
            }

            return $total;
        });

        $entries = MemoEntry::whereMonth('tanggal_memo', $bulan)
            ->whereYear('tanggal_memo', $tahun)
            ->get();

        $totalKonsumsi = $entries->where('jenis_memo', 'konsumsi')->sum('nominal');
        $totalHonorarium = $entries->where('jenis_memo', 'honorarium')->sum('nominal');

        return [
            'perdin' => $totalPerdin,
            'konsumsi' => $totalKonsumsi,
            'honorarium' => $totalHonorarium,
            'total' => $totalPerdin + $totalKonsumsi + $totalHonorarium,
        ];
    }

    private function dataKalender(Request $request): array
    {
        $now = now();
        $bulanParam = (string) $request->query('bulan', '');

        $bulan = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulanParam)
            ? Carbon::createFromFormat('!Y-m', $bulanParam)
            : $now->copy()->startOfMonth();

        $startOfMonth = $bulan->copy()->startOfMonth();
        $endOfMonth = $bulan->copy()->endOfMonth();

        $agendaList = Agenda::where('tanggal_mulai', '<=', $endOfMonth)
            ->where('tanggal_selesai', '>=', $startOfMonth)
            ->with('pegawai')
            ->orderBy('tanggal_mulai')
            ->get();

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
            'offset' => $startOfMonth->dayOfWeekIso - 1,
            'jumlahHari' => $startOfMonth->daysInMonth,
            'hariIni' => $isBulanIni ? $now->day : null,
            'isBulanIni' => $isBulanIni,
            'prev' => $startOfMonth->copy()->subMonth()->format('Y-m'),
            'next' => $startOfMonth->copy()->addMonth()->format('Y-m'),
            'marks' => $marks,
        ];
    }
}