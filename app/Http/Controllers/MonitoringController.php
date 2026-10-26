<?php

namespace App\Http\Controllers;

use App\Models\Disposisi;
use App\Models\LaporanStTugas;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\SuratTugas;
use App\Models\SuratTugasKontrol;
use App\Support\SimpleXlsx;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Monitoring terpadu: tugas yang menunggu tindakan pengguna ("notifikasi") dan rekap status
 * surat masuk, disposisi/tindak lanjut, surat keluar, serta surat tugas & laporan (SPJ).
 * Hanya membaca data; tidak mengubah alur yang sudah ada.
 */
class MonitoringController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $lihatSemua = $this->bolehLihatSemua();

        // ---------- Perlu tindakan saya ----------
        $disposisiSaya = Disposisi::with('suratMasuk')
            ->where('penerima_id', $user->id)
            ->where(function ($q) { $q->whereNull('is_tembusan')->orWhere('is_tembusan', false); })
            ->whereIn('status', ['baru', 'dibaca', 'diproses'])
            ->latest()->limit(15)->get();

        $suratKeluarSaya = collect();
        if ($user->isAdmin() || $user->isEselon2()) {
            $suratKeluarSaya = $suratKeluarSaya->merge(
                SuratKeluar::where('status_persetujuan', 'diajukan')->latest()->get()->map(fn ($s) => [$s, 'Menunggu persetujuan Eselon 2'])
            );
        }
        if ($user->isAdmin() || $user->isEselon1()) {
            $suratKeluarSaya = $suratKeluarSaya->merge(
                SuratKeluar::where('status_persetujuan', 'disetujui_es2')->latest()->get()
                    ->filter(fn ($s) => $s->butuh_eselon1)->map(fn ($s) => [$s, 'Menunggu persetujuan Eselon 1'])
            );
        }
        if ($user->isAdmin() || $user->isTu()) {
            $suratKeluarSaya = $suratKeluarSaya->merge(
                SuratKeluar::whereIn('status_persetujuan', ['disetujui_es2', 'disetujui_es1'])->latest()->get()
                    ->filter(fn ($s) => $s->status_persetujuan === 'disetujui_es1' || !$s->butuh_eselon1)
                    ->map(fn ($s) => [$s, 'Siap diterbitkan (TTD basah)'])
            );
        }
        $suratKeluarSaya = $suratKeluarSaya->merge(
            SuratKeluar::where('status_persetujuan', 'draft')->where('created_by', $user->id)->latest()->get()
                ->filter(fn ($s) => filled($s->payload('catatan_revisi')))->map(fn ($s) => [$s, 'Dikembalikan untuk revisi'])
        );

        $kontrolSaya = SuratTugasKontrol::with('suratTugas')
            ->where('petugas_id', $user->id)
            ->where('status', 'menunggu')
            ->whereHas('suratTugas', fn ($q) => $q->where('status', 'draf'))
            ->get()
            ->filter(function ($k) {
                return !SuratTugasKontrol::where('surat_tugas_id', $k->surat_tugas_id)
                    ->where('urutan', '<', $k->urutan)->where('status', '!=', 'disetujui')->exists();
            });

        $laporanSaya = LaporanStTugas::with('suratTugas')
            ->where('user_id', $user->id)
            ->where('status', LaporanStTugas::STATUS_MENUNGGU)
            ->latest()->limit(15)->get();

        // ---------- Rekap status ----------
        $rekap = null;
        if ($lihatSemua) {
            $rekap = [
                'surat_masuk_status' => SuratMasuk::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
                'surat_masuk_jalur' => SuratMasuk::selectRaw('jalur_tujuan, count(*) as total')->groupBy('jalur_tujuan')->pluck('total', 'jalur_tujuan'),
                'disposisi_status' => Disposisi::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
                'disposisi_lewat_tenggat' => Disposisi::whereNotNull('tenggat_waktu')
                    ->whereDate('tenggat_waktu', '<', now()->toDateString())
                    ->where('status', '!=', 'selesai')->count(),
                'surat_keluar' => SuratKeluar::selectRaw('status_persetujuan, count(*) as total')->groupBy('status_persetujuan')->pluck('total', 'status_persetujuan'),
                'surat_tugas' => SuratTugas::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
                'laporan_menunggu' => SuratTugas::where('status', 'terbit')->count(),
            ];
        }

        return view('monitoring.index', compact(
            'disposisiSaya', 'suratKeluarSaya', 'kontrolSaya', 'laporanSaya', 'rekap', 'lihatSemua'
        ));
    }

    /**
     * Unduh rekap Excel (.xlsx): Surat Masuk, Disposisi, Surat Keluar, Surat Tugas & Laporan.
     */
    public function export()
    {
        abort_unless($this->bolehLihatSemua(), 403, 'Rekap hanya untuk admin, TU, Eselon 1, dan Eselon 2.');

        $tgl = fn ($d) => $d ? $d->format('d/m/Y') : '';

        $suratMasuk = SuratMasuk::orderBy('id')->get()->map(fn ($s) => [
            $s->nomor_agenda, $s->nomor_surat, $s->asal_surat, $s->perihal,
            $tgl($s->tanggal_surat), $tgl($s->tanggal_diterima), $s->jalur_label, ucfirst((string) $s->status),
        ])->all();

        $disposisi = Disposisi::with(['suratMasuk', 'pengirim', 'penerima'])->orderBy('id')->get()->map(fn ($d) => [
            $d->suratMasuk->nomor_agenda ?? '', $d->suratMasuk->perihal ?? '',
            $d->pengirim->name ?? '', $d->penerima->name ?? '', ucfirst((string) $d->sifat),
            $d->tenggat_waktu ? \Carbon\Carbon::parse($d->tenggat_waktu)->format('d/m/Y') : '',
            ucfirst((string) $d->status), $d->is_tembusan ? 'Ya' : '', $d->instruksi,
        ])->all();

        $suratKeluar = SuratKeluar::with('pembuat')->orderBy('id')->get()->map(fn ($s) => [
            $s->nama_jenis, $s->nomor_surat ?: '(draf)', $s->payload('kode_klasifikasi', ''), $s->hal,
            $tgl($s->tanggal_surat), $s->tujuan, $s->status_label, $s->pembuat->name ?? '',
        ])->all();

        $suratTugas = SuratTugas::with(['peserta', 'laporan'])->orderBy('id')->get()->map(fn ($s) => [
            $s->nomor ?: '(draf)', $tgl($s->tanggal), $s->status_label,
            $s->peserta->pluck('nama')->implode(', '),
            $s->laporan ? ($s->laporan->status === 'dikirim' ? 'Dikirim' : 'Draf') : 'Belum diisi',
        ])->all();

        $bytes = SimpleXlsx::build([
            'Surat Masuk' => ['headers' => ['No. Agenda', 'No. Surat', 'Asal Surat', 'Perihal', 'Tgl Surat', 'Tgl Diterima', 'Jalur', 'Status'], 'rows' => $suratMasuk],
            'Disposisi' => ['headers' => ['No. Agenda', 'Perihal', 'Dari', 'Kepada', 'Sifat', 'Tenggat', 'Status', 'Tembusan', 'Instruksi / Tindak Lanjut'], 'rows' => $disposisi],
            'Surat Keluar' => ['headers' => ['Jenis', 'Nomor', 'Kode Klasifikasi', 'Hal', 'Tanggal', 'Tujuan', 'Status', 'Pembuat'], 'rows' => $suratKeluar],
            'Surat Tugas & Laporan' => ['headers' => ['Nomor', 'Tanggal', 'Status', 'Peserta', 'Laporan (SPJ)'], 'rows' => $suratTugas],
        ]);

        $nama = 'rekap-persuratan-' . now()->format('Ymd-His') . '.xlsx';

        return response($bytes, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
            'Content-Length' => (string) strlen($bytes),
        ]);
    }

    private function bolehLihatSemua(): bool
    {
        $u = Auth::user();

        return $u && ($u->isAdmin() || $u->isTu() || $u->isEselon1() || $u->isEselon2());
    }
}
