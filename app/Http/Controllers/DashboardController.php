<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
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
        // Dana diajukan (LS) bulan ini, lengkap dengan breakdown per jenis
        // (Perdin / Konsumsi / Honorarium) — dipakai di card ringkasan.
        $danaLsBulanIni = $this->danaLsBulan($now->month, $now->year);
        $totalDanaBulanIni = $danaLsBulanIni['total'];

        // Data chart per bulan (Jan - Des) tahun berjalan — cuma butuh totalnya
        $chartLabels = [];
        $chartData = [];
        for ($i = 1; $i <= 12; $i++) {
            $chartLabels[] = Carbon::create()->month($i)->translatedFormat('M');
            $chartData[] = $this->danaLsBulan($i, $now->year)['total'];
        }

        // Data awal kalender (bulan dari ?bulan=YYYY-MM, default bulan berjalan)
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

    /**
     * Endpoint JSON untuk pindah bulan kalender tanpa reload halaman.
     * GET /dashboard/kalender?bulan=2026-10
     */
    public function kalender(Request $request)
    {
        return response()->json($this->dataKalender($request));
    }

    /**
     * Dana yang diajukan lewat LS pada bulan & tahun tertentu, dipecah per
     * jenis, gabungan dari dua sumber (sama persis dengan yang dipakai
     * MemoController@index buat nampilin daftar nomor memo):
     * - Perdin: dari Agenda yang nomor_memo_pns/non_pns sudah diisi
     *   (nominalnya biaya_asn utk PNS, biaya_non_asn utk Non PNS)
     * - Konsumsi & Honorarium: dari MemoEntry (nomor memo mandiri),
     *   dipisah berdasarkan kolom jenis_memo
     *
     * Difilter berdasarkan tanggal_mulai (Agenda) / tanggal_memo (MemoEntry).
     */
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