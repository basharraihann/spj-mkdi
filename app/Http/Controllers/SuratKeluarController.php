<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SuratKeluarController extends Controller
{
    /**
     * Tampilkan daftar monitoring Surat Keluar & Draf Dokumen.
     * Pegawai (role user) hanya melihat dokumennya sendiri; role lain melihat semua.
     * Tambahkan ?export=csv untuk unduh rekap (bisa dibuka di Excel).
     */
    public function index(Request $request): View|StreamedResponse
    {
        $query = SuratKeluar::with('pembuat');

        $pengguna = Auth::user();
        if ($pengguna?->role === 'user') {
            $query->where('created_by', Auth::id());
        } elseif ($this->dibatasiUnit($pengguna)) {
            // TU masing-masing unit: dokumen unit lain tidak ditampilkan
            $query->whereHas('pembuat', fn ($p) => $p->where('unit_id', $pengguna->unit_id));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('hal', 'like', "%{$search}%")
                    ->orWhere('nomor_surat', 'like', "%{$search}%")
                    ->orWhere('tujuan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis_dokumen', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status_persetujuan', $request->status);
        }

        if ($request->query('export') === 'csv') {
            return $this->exportCsv($query->latest()->get());
        }

        $suratKeluar = $query->latest()->paginate(10)->withQueryString();

        return view('surat-keluar.index', compact('suratKeluar'));
    }

    /**
     * Form pembuatan draf Surat Keluar / Dokumen baru.
     */
    public function create(): View
    {
        return view('surat-keluar.create');
    }

    /**
     * Simpan draf Surat Keluar / Dokumen ke database.
     * Draf hanya tersimpan di sistem; PDF dibuat saat dicetak, tidak otomatis disimpan.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        // Isian jenis dokumen dibaca utuh dari request (validated() hanya memuat kunci yang punya aturan khusus)
        $data = $this->normalisasi($validated, $this->bersihkanPayload($request->input('isi_payload')));

        $suratKeluar = SuratKeluar::create($data + [
            'created_by' => Auth::id(),
            'status_persetujuan' => 'draft',
        ]);

        return redirect()->route('surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Draf ' . $suratKeluar->nama_jenis . ' berhasil dibuat.');
    }

    /**
     * Tampilkan detail & pratinjau Surat Keluar.
     */
    public function show(SuratKeluar $suratKeluar): View
    {
        $suratKeluar->load('pembuat');

        $pengguna = Auth::user();
        if ($this->dibatasiUnit($pengguna) && !$this->satuUnit($suratKeluar, $pengguna)) {
            abort(403, 'Dokumen ini bukan milik unit Anda.');
        }

        return view('surat-keluar.show', compact('suratKeluar'));
    }

    /**
     * Form edit Surat Keluar / Dokumen.
     */
    public function edit(SuratKeluar $suratKeluar): View|RedirectResponse
    {
        if ($tolak = $this->tolakJikaTidakBolehUbah($suratKeluar)) {
            return $tolak;
        }

        return view('surat-keluar.edit', compact('suratKeluar'));
    }

    /**
     * Update data Surat Keluar.
     */
    public function update(Request $request, SuratKeluar $suratKeluar): RedirectResponse
    {
        if ($tolak = $this->tolakJikaTidakBolehUbah($suratKeluar)) {
            return $tolak;
        }

        $validated = $request->validate($this->rules());
        // Gabungkan isian form dengan data sistem yang sudah ada (catatan revisi, tanggal kirim, dst.)
        $payloadBaru = array_merge(
            (array) ($suratKeluar->isi_payload ?? []),
            $this->bersihkanPayload($request->input('isi_payload'))
        );
        $data = $this->normalisasi($validated, $payloadBaru);
        // Perilaku lama dipertahankan: nomor surat tidak terhapus bila dikosongkan saat edit
        $data['nomor_surat'] = $data['nomor_surat'] ?? $suratKeluar->nomor_surat;

        $suratKeluar->update($data);

        return redirect()->route('surat-keluar.show', $suratKeluar->id)
            ->with('success', 'Data ' . $suratKeluar->nama_jenis . ' berhasil diperbarui.');
    }

    /**
     * Hapus data Surat Keluar.
     */
    public function destroy(SuratKeluar $suratKeluar): RedirectResponse
    {
        if ($tolak = $this->tolakJikaTidakBolehUbah($suratKeluar)) {
            return $tolak;
        }

        $namaJenis = $suratKeluar->nama_jenis;
        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')
            ->with('success', 'Dokumen ' . $namaJenis . ' berhasil dihapus.');
    }

    // =========================================================
    // TAMBAHAN: alur persetujuan (lembar kontrol sederhana)
    // draft -> diajukan (periksa Es 3) -> diperiksa_es3 -> disetujui_es2 -> (disetujui_es1) -> terbit -> terkirim
    // =========================================================

    public function ubahStatus(Request $request, SuratKeluar $suratKeluar): RedirectResponse
    {
        $request->validate([
            'aksi' => ['required', 'in:ajukan,periksa_es3,setujui_es2,setujui_es1,kembalikan,terbit,kirim'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'file_scan_ttd' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);

        $user = Auth::user();
        $status = $suratKeluar->status_persetujuan;
        $admin = $user->isAdmin();
        $pesan = null;

        switch ($request->aksi) {
            case 'ajukan':
                $boleh = $status === 'draft' && ($admin || $user->isTu() || $suratKeluar->created_by === $user->id);
                if ($boleh) {
                    $suratKeluar->status_persetujuan = 'diajukan';
                    $pesan = 'Dokumen diajukan untuk diperiksa Eselon 3 / Madya.';
                }
                break;

            case 'periksa_es3':
                if ($status === 'diajukan' && ($admin || ($user->isEselon3() && $this->satuUnit($suratKeluar, $user)))) {
                    $suratKeluar->status_persetujuan = 'diperiksa_es3';
                    $pesan = 'Konsep sudah diperiksa Eselon 3, menunggu persetujuan Eselon 2.';
                }
                break;

            case 'setujui_es2':
                if ($status === 'diperiksa_es3' && ($admin || ($user->isEselon2() && $this->satuUnit($suratKeluar, $user)))) {
                    $suratKeluar->status_persetujuan = 'disetujui_es2';
                    $pesan = $suratKeluar->butuh_eselon1
                        ? 'Disetujui Eselon 2, menunggu pemeriksaan Eselon 1.'
                        : 'Disetujui Eselon 2, lanjut proses penandatanganan (TTD/TTE).';
                }
                break;

            case 'setujui_es1':
                if ($status === 'disetujui_es2' && $suratKeluar->butuh_eselon1 && ($admin || $user->isEselon1())) {
                    $suratKeluar->status_persetujuan = 'disetujui_es1';
                    $pesan = 'Disetujui Eselon 1, siap diterbitkan.';
                }
                break;

            case 'kembalikan':
                if ($suratKeluar->bolehDikembalikanOleh($user) && ($admin || $user->isEselon1() || $this->satuUnit($suratKeluar, $user))) {
                    $payload = $suratKeluar->isi_payload ?? [];
                    $payload['catatan_revisi'] = $request->input('catatan');
                    $suratKeluar->isi_payload = $payload;
                    $suratKeluar->status_persetujuan = 'draft';
                    $pesan = 'Dokumen dikembalikan ke pembuat untuk direvisi.';
                }
                break;

            case 'terbit':
                if ($suratKeluar->siap_terbit && ($admin || ($user->isTu() && $this->satuUnit($suratKeluar, $user)))) {
                    // Penomoran dilakukan TU saat menerima surat yang sudah ditandatangani
                    $nomor = trim((string) $request->input('nomor_surat')) ?: $suratKeluar->nomor_surat;
                    if (blank($nomor)) {
                        return back()->withErrors(['nomor_surat' => 'Nomor surat wajib diisi saat menerima surat TTD (penomoran).'])->withInput();
                    }
                    $suratKeluar->nomor_surat = $nomor;

                    if ($request->hasFile('file_scan_ttd')) {
                        $suratKeluar->file_scan_ttd = $request->file('file_scan_ttd')
                            ->store('surat-keluar/ttd', 'public');
                    }
                    $suratKeluar->status_persetujuan = 'terbit';
                    $pesan = 'Surat TTD diterima dan diberi nomor, siap dikirim ke unit tujuan.';
                }
                break;

            case 'kirim':
                if ($status === 'terbit' && ($admin || ($user->isTu() && $this->satuUnit($suratKeluar, $user)))) {
                    $payload = $suratKeluar->isi_payload ?? [];
                    $payload['tanggal_kirim'] = now()->toDateString();
                    $suratKeluar->isi_payload = $payload;
                    $suratKeluar->status_persetujuan = 'terkirim';
                    $pesan = 'Surat sudah dikirim ke unit tujuan. Proses selesai.';
                }
                break;
        }

        if ($pesan === null) {
            return back()->withErrors(['aksi' => 'Aksi tidak dapat dilakukan pada status atau hak akses Anda saat ini.']);
        }

        $suratKeluar->save();

        return redirect()->route('surat-keluar.show', $suratKeluar->id)->with('success', $pesan);
    }

    // =========================================================
    // Helper privat
    // =========================================================

    private function rules(): array
    {
        return [
            'jenis_dokumen' => ['required', 'in:nota_dinas,undangan,telaah_staf,notula,surat_pernyataan,laporan'],
            'nomor_surat' => ['nullable', 'string', 'max:100'],
            'sifat' => ['required', 'in:biasa,terbatas,rahasia'],
            'hal' => ['required', 'string', 'max:255'],
            'tanggal_surat' => ['required', 'date'],
            'tujuan' => ['nullable', 'string', 'max:255'],
            'dari' => ['nullable', 'string', 'max:255'],
            'isi_payload' => ['required', 'array'],
            'isi_payload.kode_klasifikasi' => ['nullable', 'string', 'max:50'],
            'isi_payload.template' => ['nullable', 'string', 'max:50'],
            'tembusan' => ['nullable'],
        ];
    }

    /** TU, Eselon 3, dan Eselon 2 yang terhubung ke sebuah unit hanya menangani dokumen unitnya. */
    private function dibatasiUnit($user): bool
    {
        return $user && $user->unit_id
            && ($user->isTu() || $user->isEselon3() || $user->isEselon2())
            && !$user->isAdmin();
    }

    /** Pembuat dokumen berada di unit yang sama dengan pengguna (pembuat tanpa unit tidak dibatasi). */
    private function satuUnit(SuratKeluar $suratKeluar, $user): bool
    {
        $unitPembuat = $suratKeluar->pembuat?->unit_id;

        return $unitPembuat === null || !$user->unit_id || (int) $unitPembuat === (int) $user->unit_id;
    }

    /** Ambil isi_payload dari request: hanya kunci teks; nilai berupa teks/angka atau daftar teks. */
    private function bersihkanPayload($payload): array
    {
        $bersih = [];

        foreach ((array) $payload as $kunci => $nilai) {
            if (!is_string($kunci)) {
                continue;
            }

            if (is_array($nilai)) {
                $bersih[$kunci] = array_values(array_map(
                    fn ($x) => is_scalar($x) ? (string) $x : '',
                    $nilai
                ));
            } elseif (is_scalar($nilai) || $nilai === null) {
                $bersih[$kunci] = $nilai;
            }
        }

        return $bersih;
    }

    /** Rapikan hasil validasi menjadi atribut model. */
    private function normalisasi(array $v, ?array $payload = null): array
    {
        $payload = $payload ?? ($v['isi_payload'] ?? []);
        $payload['dengan_lampiran'] = !empty($payload['dengan_lampiran']) ? 1 : 0;
        $payload['perlu_es1'] = !empty($payload['perlu_es1']) ? 1 : 0;

        // Tembusan: textarea (satu per baris) ATAU array
        $tembusan = $v['tembusan'] ?? [];
        if (is_string($tembusan)) {
            $tembusan = preg_split('/\R/', $tembusan);
        }
        $tembusan = array_values(array_filter(array_map('trim', (array) $tembusan), 'strlen'));

        $tujuan = $v['tujuan'] ?? null;
        $dari = $v['dari'] ?? null;

        // Isian bawaan Nota Dinas mengikuti template resmi bila dikosongkan
        if ($v['jenis_dokumen'] === 'nota_dinas') {
            $es1 = ($payload['template'] ?? 'es2') === 'es1_deputi2';
            $tujuan = $tujuan ?: ($es1 ? 'Sekretaris Kementerian Koordinator' : 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian');
            $dari = $dari ?: ($es1 ? 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian' : 'Sekretaris Deputi Bidang Koordinasi Usaha Pangan dan Pertanian');
            if (empty($tembusan)) {
                $tembusan = [$es1 ? 'Menteri Koordinator Bidang Pangan' : 'Deputi Bidang Koordinasi Usaha Pangan dan Pertanian'];
            }
        }

        return [
            'jenis_dokumen' => $v['jenis_dokumen'],
            'nomor_surat' => $v['nomor_surat'] ?? null,
            'sifat' => $v['sifat'],
            'hal' => $v['hal'],
            'tanggal_surat' => $v['tanggal_surat'],
            'tujuan' => $tujuan,
            'dari' => $dari,
            'isi_payload' => $payload,
            'tembusan' => $tembusan,
        ];
    }

    /** Edit/hapus hanya untuk draf, oleh pembuatnya atau admin/TU. */
    private function tolakJikaTidakBolehUbah(SuratKeluar $suratKeluar): ?RedirectResponse
    {
        $user = Auth::user();
        $admin = $user->isAdmin();

        if (!$suratKeluar->bisa_diubah && !$admin) {
            return redirect()->route('surat-keluar.show', $suratKeluar->id)
                ->withErrors(['aksi' => 'Dokumen yang sudah diajukan tidak dapat diubah. Minta dikembalikan terlebih dahulu.']);
        }

        if (!$admin && !$user->isTu() && $suratKeluar->created_by !== $user->id) {
            abort(403, 'Anda tidak berhak mengubah dokumen ini.');
        }

        if (!$admin && $user->isTu() && $suratKeluar->created_by !== $user->id && !$this->satuUnit($suratKeluar, $user)) {
            abort(403, 'Dokumen ini bukan milik unit Anda.');
        }

        return null;
    }

    private function exportCsv($rows): StreamedResponse
    {
        $nama = 'rekap-surat-keluar-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel membaca UTF-8
            fputcsv($out, ['No', 'Jenis', 'Nomor Surat', 'Kode Klasifikasi', 'Hal', 'Tanggal', 'Tujuan', 'Status', 'Pembuat']);
            foreach ($rows as $i => $r) {
                fputcsv($out, [
                    $i + 1,
                    $r->nama_jenis,
                    $r->nomor_surat,
                    $r->payload('kode_klasifikasi'),
                    $r->hal,
                    optional($r->tanggal_surat)->format('Y-m-d'),
                    $r->tujuan,
                    $r->status_label,
                    $r->pembuat?->name,
                ]);
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
