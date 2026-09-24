<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;

class AgendaPdfController extends Controller
{
    /**
     * Mapping key komponen_biaya (chip di halaman peserta) ke label Indonesia
     * yang rapi buat ditampilkan di lampiran memorandum, sebagai daftar
     * "- item," di bawah uraian belanja (lihat memorandumPdf()).
     */
    private const KOMPONEN_LABELS = [
        'tiket' => 'Tiket',
        'dukungan_transportasi' => 'Dukungan Transportasi',
        'transportasi_darat' => 'Transportasi Darat',
        'transportasi_lokal' => 'Transportasi Lokal',
        'peng_riil' => 'Pengeluaran Riil',
        'hotel' => 'Biaya Penginapan/Hotel',
        'penginapan_30' => 'Penginapan (At Cost 30%)',
        'representatif' => 'Uang Representatif',
        'belanja_bahan' => 'Belanja Bahan',
        'honor_narsum' => 'Honor Narasumber',
        'lumpsum' => 'Uang Harian',
    ];

    public function generateSpd(Agenda $agenda, Pegawai $pegawai)
    {
        $agenda->load('ppk');

        // Kepala Biro (Karo) pakai nomor ST sendiri, beda dari nomor_st yang
        // dipakai bareng semua peserta lain di agenda yang sama. Kalau bukan
        // Karo, atau nomor_st_karo belum diisi, tetap pakai nomor_st biasa.
        $isKaro = str_contains(strtolower($pegawai->jabatan ?? ''), 'kepala biro');
        $nomorSt = ($isKaro && $agenda->nomor_st_karo)
            ? $agenda->nomor_st_karo
            : $agenda->nomor_st;

        $pdf = Pdf::loadView('pdf.spd', [
            'agenda' => $agenda,
            'pegawai' => $pegawai,
            'ppk' => $agenda->ppk,
            'nomorSt' => $nomorSt,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("SPD-{$pegawai->nama}-{$agenda->id}.pdf");
    }

    public function generateNominatif(Agenda $agenda, string $status)
    {
        if (!in_array($status, ['pns', 'non-pns'])) {
            abort(404);
        }

        $statusLabel = $status === 'pns' ? 'PNS' : 'Non PNS';

        $agenda->load(['pegawai', 'ppk', 'bendahara', 'penanggungJawab']);

        // filter cuma pegawai dengan status yang sesuai
        $pegawaiFiltered = $agenda->pegawai->where('status_kepegawaian', $statusLabel)->values();

        $pdf = Pdf::loadView('pdf.nominatif', [
            'agenda' => $agenda,
            'pegawaiList' => $pegawaiFiltered,
            'statusLabel' => $statusLabel,
            'ppk' => $agenda->ppk,
            'bendahara' => $agenda->bendahara,
            'penanggungJawab' => $agenda->penanggungJawab,
        ])->setPaper('a4', 'landscape');

        return $pdf->stream("Nominatif-{$statusLabel}-{$agenda->id}.pdf");
    }

    public function memorandumPdf(Agenda $agenda, string $status)
    {
        $this->validateMemoStatus($status);
        $suffix = $status === 'pns' ? 'pns' : 'non_pns';
        $statusLabel = $status === 'pns' ? 'PNS' : 'Non PNS';

        $agenda->load(['penanggungJawab', 'ppk', 'bendahara', 'petugasVerifikasi', 'pegawai']);

        // Daftar rincian biaya (mis. "Pengeluaran Riil", "Uang Harian", dst) yang
        // ditampilkan di lampiran memorandum halaman 2, diturunkan dari komponen
        // biaya yang dipilih di halaman peserta (chip "Komponen Biaya").
        $rincianBiaya = collect($agenda->komponen_biaya ?? [])
            ->map(fn($key) => self::KOMPONEN_LABELS[$key] ?? ucfirst(str_replace('_', ' ', $key)))
            ->values()
            ->all();

        $pdf = Pdf::loadView('pdf.memorandum', [
            'agenda' => $agenda,
            'statusLabel' => $statusLabel,
            'nomorMemo' => $agenda->{"nomor_memo_{$suffix}"},
            'uraianMemo' => $agenda->{"uraian_memo_{$suffix}"},
            'rincianTransfer' => $agenda->rincianTransfer($statusLabel),
            'rincianBiaya' => $rincianBiaya,
            'totalBiaya' => $status === 'pns' ? $agenda->biaya_asn : $agenda->biaya_non_asn,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Memorandum-{$statusLabel}-{$agenda->id}.pdf");
    }

    public function generateRincianBiaya(Agenda $agenda, Pegawai $pegawai)
    {
        $agenda->load(['ppk', 'bendahara']);
        $pivot = $agenda->pegawai()->where('pegawai.id', $pegawai->id)->first()?->pivot;

        $pdf = Pdf::loadView('pdf.rincian-biaya', [
            'agenda' => $agenda,
            'pegawai' => $pegawai,
            'pivot' => $pivot,
            'ppk' => $agenda->ppk,
            'bendahara' => $agenda->bendahara,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("RincianBiaya-{$pegawai->nama}-{$agenda->id}.pdf");
    }

    public function generatePengeluaranRiil(Agenda $agenda, Pegawai $pegawai)
    {
        $agenda->load('ppk');
        $pivot = $agenda->pegawai()->where('pegawai.id', $pegawai->id)->first()?->pivot;

        $pdf = Pdf::loadView('pdf.pengeluaran-riil', [
            'agenda' => $agenda,
            'pegawai' => $pegawai,
            'pivot' => $pivot,
            'ppk' => $agenda->ppk,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("PengeluaranRiil-{$pegawai->nama}-{$agenda->id}.pdf");
    }

    public function generateMergedPdf(Agenda $agenda, Pegawai $pegawai)
    {
        $agenda->load(['ppk', 'bendahara']);
        $pivot = $agenda->pegawai()->where('pegawai.id', $pegawai->id)->first()?->pivot;

        $pdf = Pdf::loadView('pdf.merged', [
            'agenda' => $agenda,
            'pegawai' => $pegawai,
            'pivot' => $pivot,
            'ppk' => $agenda->ppk,
            'bendahara' => $agenda->bendahara,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Merged-{$pegawai->nama}-{$agenda->id}.pdf");
    }

    private function validateMemoStatus(string $status): void
    {
        abort_unless(in_array($status, ['pns', 'non-pns']), 404);
    }
}