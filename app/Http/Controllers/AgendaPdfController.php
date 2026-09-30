<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\Pegawai;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

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

        // Tujuan Jakarta pakai format nominatif khusus (pdf.nominatif-jakarta),
        // selain itu pakai format biasa (pdf.nominatif).
        $view = $this->isTujuanJakarta($agenda) ? 'pdf.nominatif-jakarta' : 'pdf.nominatif';

        $pdf = Pdf::loadView($view, [
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

        // Daftar rincian biaya yang ditampilkan di lampiran memorandum halaman 2,
        // diturunkan dari komponen biaya yang dipilih di halaman peserta.
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
            'lampiran' => $this->buildLampiran($agenda, $statusLabel), // halaman 3
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Memorandum-{$statusLabel}-{$agenda->id}.pdf");
    }

    /**
     * Data halaman 3 (Lampiran Memorandum): rincian item, total bersih per
     * pegawai, info ST, dan perihal. Hanya pegawai dengan status yang sesuai
     * (PNS / Non PNS) yang dihitung. Rumus mengikuti Agenda::hitungBiaya().
     */
    private function buildLampiran(Agenda $agenda, string $statusLabel): array
    {
        $peserta = $agenda->pegawai
            ->where('status_kepegawaian', $statusLabel)
            ->values();

        // Jumlahkan tiap item dari semua peserta, buang yang totalnya 0
        $totalPerItem = [];
        foreach ($peserta as $p) {
            foreach ($this->itemBiayaPeserta($p->pivot) as $label => $nilai) {
                $totalPerItem[$label] = ($totalPerItem[$label] ?? 0) + $nilai;
            }
        }

        $items = [];
        foreach ($totalPerItem as $label => $jumlah) {
            if ($jumlah > 0) {
                $items[] = ['item' => $label, 'jumlah' => $jumlah];
            }
        }

        // Total bersih per pegawai: pakai rincianTransfer() biar konsisten
        // dengan total biaya di memorandum.
        $pegawai = $agenda->rincianTransfer($statusLabel)
            ->map(fn($r) => [
                'nama' => $r['nama'],
                'total' => (float) $r['total_bersih'],
            ])
            ->all();

        // Cari tanggal ST dari beberapa kemungkinan nama kolom di tabel agendas.
        // Kalau nama kolom lu beda, tambahkan ke daftar ini.
        $tanggalSt = null;
        foreach (['tanggal_st', 'tgl_st', 'tanggal_surat_tugas', 'tanggal_surat', 'tgl_surat_tugas'] as $kolom) {
            if (!empty($agenda->{$kolom})) {
                $tanggalSt = $agenda->{$kolom};
                break;
            }
        }

        // Kolom tanggal ST belum ada, jadi pakai tanggal mulai kegiatan.
        $tanggalSt ??= $agenda->tanggal_mulai;
        $stInfo = 'ST No. ' . ($agenda->nomor_st ?? '-');
        if ($tanggalSt) {
            $stInfo .= ' Tanggal ' . Carbon::parse($tanggalSt)->translatedFormat('d F Y');
        }

        return [
            'perihal' => $agenda->uraian_kegiatan,
            'st_info' => $stInfo,
            'items' => $items,
            'pegawai' => $pegawai,
        ];
    }

    /**
     * Pecah biaya satu peserta ke item-item lampiran. Urutan mengikuti contoh
     * lampiran; Uang Harian dipecah per jenis (hari x rate), totalnya sama
     * dengan lumpsum di Agenda::hitungBiaya().
     */
    private function itemBiayaPeserta($pivot): array
    {
        $v = fn($k) => (float) ($pivot->{$k} ?? 0);

        return [
            'UH Dinas Biasa' => $v('hari_dinas_biasa') * $v('rate_dinas_biasa'),
            'UH Biasa 60%' => $v('hari_biasa_60') * $v('rate_biasa_60'),
            'UH Fullday' => $v('hari_fullday') * $v('rate_fullday'),
            'UH Fullboard' => $v('hari_fullboard') * $v('rate_fullboard'),
            'Hotel' => $v('hotel') + $v('penginapan_30'),
            'Peng. Riil' => $v('peng_riil'),
            'Tiket' => $v('tiket'),
            'Uang Representatif' => $v('representatif'),
            'Dukungan Transportasi' => $v('dukungan_transportasi'),
            'Transportasi Darat' => $v('transportasi_darat'),
            'Transportasi Lokal' => $v('transportasi_lokal'),
            'Belanja Bahan' => $v('belanja_bahan'),
            'Honor Narasumber' => $v('honor_narsum'),
        ];
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

        $isKaro = str_contains(strtolower($pegawai->jabatan ?? ''), 'kepala biro');
        $nomorSt = ($isKaro && $agenda->nomor_st_karo)
            ? $agenda->nomor_st_karo
            : $agenda->nomor_st;

        $pdf = Pdf::loadView('pdf.merged', [
            'agenda' => $agenda,
            'pegawai' => $pegawai,
            'pivot' => $pivot,
            'ppk' => $agenda->ppk,
            'bendahara' => $agenda->bendahara,
            'nomorSt' => $nomorSt,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Merged-{$pegawai->nama}-{$agenda->id}.pdf");
    }

    private function validateMemoStatus(string $status): void
    {
        abort_unless(in_array($status, ['pns', 'non-pns']), 404);
    }

    /**
     * True kalau tujuan agenda (provinsi atau kab/kota) mengandung kata "jakarta".
     * Dipakai buat milih format PDF khusus Jakarta.
     */
    private function isTujuanJakarta(Agenda $agenda): bool
    {
        return str_contains(strtolower($agenda->tujuan ?? ''), 'jakarta')
            || str_contains(strtolower($agenda->kota_tujuan ?? ''), 'jakarta');
    }
}