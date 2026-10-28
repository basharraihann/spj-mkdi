<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratTugasRequest;
use App\Models\KabupatenKota;
use App\Models\Pegawai;
use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use App\Models\SuratTugasKontrol;
use App\Models\Tusi;
use App\Services\LaporanStService;
use App\Services\SuratTugasNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SuratTugasController extends Controller
{
    private const DASAR_TETAP = 'Daftar Isian Pelaksanaan Anggaran (DIPA) Satuan Kerja Kementerian Koordinator Bidang Pangan Tahun 2025 Nomor: SP DIPA-131.01-0/2026 tanggal 1 Desember 2025';

    public function __construct(private SuratTugasNumberService $numbers)
    {
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $q = SuratTugas::dapatDilihat($user)
            ->with('tusi')->withCount('peserta')
            ->latest('tanggal')->latest('id');

        if ($s = $request->query('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('nomor', 'like', "%{$s}%")
                    ->orWhere('untuk', 'like', "%{$s}%")
                    ->orWhere('menimbang', 'like', "%{$s}%");
            });
        }

        if ($filter = $request->query('tusi')) {
            $filter === 'none'
                ? $q->whereNull('tusi_id')
                : $q->where('tusi_id', (int) $filter);
        }

        $tahun = now()->year;

        return view('surat-tugas.index', [
            'items' => $q->paginate(15)->withQueryString(),
            'tahun' => $tahun,
            'counter' => SuratTugasCounter::where('tahun', $tahun)->value('last_number') ?? 0,
            'nomorBerikutnya' => $this->numbers->nomorBerikutnya($tahun, config('surat_tugas.kode_klasifikasi')),
            'isAdmin' => $this->isAdmin(),
            'tusiOptions' => Tusi::urut()->get(['id', 'nama', 'aktif']),
            'belumDikelompokkan' => SuratTugas::dapatDilihat($user)->whereNull('tusi_id')->count(),
        ]);
    }

    public function create()
    {
        $st = new SuratTugas([
            'tempat' => config('surat_tugas.tempat'),
            'tanggal' => now(),
            'kode_klasifikasi' => config('surat_tugas.kode_klasifikasi'),
            'kepada' => config('surat_tugas.kepada_default'),
            'jabatan_penandatangan' => config('surat_tugas.penandatangan.jabatan'),
            'nama_penandatangan' => config('surat_tugas.penandatangan.nama'),
            'nip_penandatangan' => config('surat_tugas.penandatangan.nip'),
            'dasar' => [],
            'untuk' => array_merge([''], config('surat_tugas.untuk_default')),
        ]);

        return view('surat-tugas.create', $this->dataForm($st, []));
    }

    public function store(SuratTugasRequest $request): RedirectResponse
    {
        $st = DB::transaction(function () use ($request) {
            $st = SuratTugas::create($this->payload($request) + [
                'status' => 'draf',
                'created_by' => auth()->id(),
                'unit_id' => auth()->user()->unit_id,
            ]);
            $this->syncPeserta($st, $request->validated('peserta'));

            return $st;
        });

        // Ambil nomor manual hanya untuk admin (jalur lama). Jalur Sesdep memberi nomor lewat lembar kontrol.
        if ($request->boolean('minta_nomor') && $this->isAdmin()) {
            $st = $this->kunciNomor($st);

            return $this->keShow($st, 'ST disimpan dengan nomor ' . $st->nomor . '.');
        }

        return $this->keShow($st, 'Draf surat tugas dibuat.');
    }

    public function show(SuratTugas $suratTugas, LaporanStService $laporan)
    {
        abort_unless($this->bolehLihat($suratTugas), 403);

        return view('surat-tugas.show', [
            'st' => $suratTugas->load('peserta', 'tusi', 'kontrol.petugas'),
            'tusiOptions' => Tusi::aktif()->urut()->get(['id', 'nama']),
            'bolehUbahTusi' => $this->bolehUbahTusi($suratTugas),
            'bolehKelola' => $this->bolehKelola($suratTugas),
            'bolehEdit' => $this->bolehEdit($suratTugas),
            'bolehTerbitkan' => $this->bolehTerbitkan($suratTugas),
            'laporan' => $suratTugas->laporan()->with('tindakLanjut', 'dikirimOleh')->first(),
            'tugasLapor' => $suratTugas->tugasLapor()->with('user:id,name')->get(),
            'bolehIsiLaporan' => $laporan->bolehIsi($suratTugas, auth()->user()),
        ]);
    }

    public function edit(SuratTugas $suratTugas)
    {
        abort_unless($this->bolehKelola($suratTugas), 403);

        if (!$suratTugas->isDraf()) {
            return $this->keShow($suratTugas, 'ST ini dikunci dan tidak bisa diedit.', 'error');
        }
        if ($suratTugas->terkunciKontrol()) {
            return $this->keShow($suratTugas, 'ST sedang dalam proses persetujuan dan tidak bisa diedit.', 'error');
        }

        return view('surat-tugas.edit', $this->dataForm($suratTugas, $this->pesertaUntukForm($suratTugas)));
    }

    public function update(SuratTugasRequest $request, SuratTugas $suratTugas): RedirectResponse
    {
        abort_unless($this->bolehKelola($suratTugas), 403);

        if (!$suratTugas->isDraf()) {
            return back()->with('error', 'ST ini dikunci.');
        }
        if ($suratTugas->terkunciKontrol()) {
            return back()->with('error', 'ST sedang dalam proses persetujuan dan tidak bisa diedit.');
        }
        if (
            $suratTugas->nomor_urut
            && Carbon::parse($request->validated('tanggal'))->year !== (int) $suratTugas->tahun
        ) {
            return back()->withInput()
                ->with('error', 'Tahun tanggal surat tidak boleh diubah setelah nomor diambil.');
        }

        DB::transaction(function () use ($request, $suratTugas) {
            $suratTugas->update($this->payload($request, $suratTugas));
            $this->syncPeserta($suratTugas, $request->validated('peserta'));
        });

        if ($request->boolean('minta_nomor') && $this->isAdmin() && !$suratTugas->nomor_urut) {
            $suratTugas = $this->kunciNomor($suratTugas->refresh());

            return $this->keShow($suratTugas, 'ST disimpan dengan nomor ' . $suratTugas->nomor . '.');
        }

        return $this->keShow($suratTugas, 'Surat tugas diperbarui.');
    }

    public function destroy(SuratTugas $suratTugas): RedirectResponse
    {
        abort_unless($this->bolehKelola($suratTugas), 403);

        if (!$suratTugas->isDraf()) {
            return $this->keShow($suratTugas, 'ST yang sudah terbit tidak bisa dihapus.', 'error');
        }
        if ($suratTugas->terkunciKontrol()) {
            return $this->keShow($suratTugas, 'ST sedang dalam proses persetujuan dan tidak bisa dihapus.', 'error');
        }

        DB::transaction(function () use ($suratTugas) {
            $suratTugas->kontrol()->delete();
            $suratTugas->peserta()->delete();
            $suratTugas->delete();
        });

        return redirect()->route('surat-tugas.index')->with('success', 'Surat tugas dihapus.');
    }

    public function updateTusi(Request $request, SuratTugas $suratTugas): RedirectResponse
    {
        abort_unless($this->bolehUbahTusi($suratTugas), 403);

        $data = $request->validate([
            'tusi_id' => [
                'nullable',
                Rule::exists('tusi', 'id')->where(
                    fn($q) => $q->where('aktif', true)->orWhere('id', $suratTugas->tusi_id)
                )
            ],
        ]);

        $suratTugas->tusi_id = $data['tusi_id'] ?: null;
        $suratTugas->save();

        return $this->keShow($suratTugas, 'Pengelompokan ST diperbarui.');
    }

    /**
     * Terbitkan ST.
     *
     * Jalur Sesdep (unit berkode "... D<n>" atau sudah punya lembar kontrol):
     *  - hanya petugas tahap kontrol (admin TU Sesdep) atau admin,
     *  - lembar kontrol harus ada dan semua tahap disetujui,
     *  - ST harus sudah bernomor (nomor diambil saat tahap kontrol disetujui),
     *  - wajib unggah PDF hasil TTD.
     * Jalur lama (mis. Biro MKDI): admin atau pembuat, file TTD opsional.
     */
    public function terbitkan(Request $request, SuratTugas $suratTugas, LaporanStService $laporan): RedirectResponse
    {
        abort_unless($suratTugas->isDraf() && $this->petugasTerbit($suratTugas), 403);

        $jalurSesdep = $this->jalurSesdep($suratTugas);

        if ($jalurSesdep) {
            if (!$suratTugas->kontrol()->exists()) {
                return $this->keShow($suratTugas, 'ST belum diajukan untuk persetujuan.', 'error');
            }
            if (!$this->semuaTahapDisetujui($suratTugas)) {
                return $this->keShow($suratTugas, 'Lembar kontrol belum selesai disetujui, ST belum dapat diterbitkan.', 'error');
            }
            if (!$suratTugas->nomor_urut) {
                return $this->keShow($suratTugas, 'ST belum bernomor. Nomor diberikan saat tahap kontrol disetujui.', 'error');
            }
        }

        $request->validate([
            'file_ttd' => [$jalurSesdep ? 'required' : 'nullable', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file_ttd.required' => 'File ST yang sudah ditandatangani (PDF) wajib diunggah.',
            'file_ttd.mimes' => 'File TTD harus berformat PDF.',
            'file_ttd.max' => 'Ukuran file TTD maksimal 10 MB.',
        ]);

        $file = $request->file('file_ttd');
        $path = null;

        try {
            [$st, $jumlahAkun] = DB::transaction(function () use ($suratTugas, $laporan, $file, &$path) {
                if ($file) {
                    $path = $file->store('surat-tugas/' . $suratTugas->id . '/ttd', LaporanStService::DISK);
                }

                $st = $this->numbers->terbitkan($suratTugas->id);

                if ($file) {
                    $st->update([
                        'file_ttd_path' => $path,
                        'file_ttd_nama' => mb_substr($file->getClientOriginalName(), 0, 180),
                        'ditandatangani_by' => auth()->id(),
                        'ditandatangani_at' => now(),
                    ]);
                }

                return [$st, $laporan->buatTugas($st)];
            });
        } catch (\DomainException $e) {
            if ($path) {
                Storage::disk(LaporanStService::DISK)->delete($path);
            }

            return $this->keShow($suratTugas, $e->getMessage(), 'error');
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk(LaporanStService::DISK)->delete($path);
            }
            throw $e;
        }

        $pesan = 'ST diterbitkan dengan nomor ' . $st->nomor . '. ';
        $pesan .= $jumlahAkun > 0
            ? "Tugas laporan dikirim ke {$jumlahAkun} akun peserta."
            : 'Belum ada peserta yang akunnya dipetakan ke pegawai, jadi belum ada tugas laporan.';

        return $this->keShow($st, $pesan, $jumlahAkun > 0 ? 'success' : 'warning');
    }

    /** Unduh file ST yang sudah ditandatangani. */
    public function unduhTtd(SuratTugas $suratTugas)
    {
        abort_unless($this->bolehLihat($suratTugas), 403);
        abort_unless(
            $suratTugas->file_ttd_path && Storage::disk(LaporanStService::DISK)->exists($suratTugas->file_ttd_path),
            404
        );

        return Storage::disk(LaporanStService::DISK)
            ->response($suratTugas->file_ttd_path, $suratTugas->file_ttd_nama ?: 'surat-tugas-ttd.pdf');
    }

    // ------------------------------------------------------------------
    // Aturan akses
    // ------------------------------------------------------------------

    private function bolehLihat(SuratTugas $st): bool
    {
        $user = auth()->user();

        return $user && SuratTugas::dapatDilihat($user)->whereKey($st->id)->exists();
    }

    private function bolehKelola(SuratTugas $st): bool
    {
        return $this->isAdmin() || (int) $st->created_by === (int) auth()->id();
    }

    private function bolehEdit(SuratTugas $st): bool
    {
        return $st->isDraf() && $this->bolehKelola($st) && !$st->terkunciKontrol();
    }

    /** Jalur Sesdep: sudah punya lembar kontrol, atau unit pembuat berkode "... D<n>". Lihat SuratTugas::memakaiAlurSesdep(). */
    private function jalurSesdep(SuratTugas $st): bool
    {
        return $st->memakaiAlurSesdep();
    }

    private function semuaTahapDisetujui(SuratTugas $st): bool
    {
        $kontrol = $st->kontrol()->get();

        return $kontrol->isNotEmpty() && $kontrol->every(fn($k) => $k->status === 'disetujui');
    }

    /** Siapa yang boleh menekan Terbitkan (peran saja, belum menilai kesiapan ST). */
    private function petugasTerbit(SuratTugas $st): bool
    {
        if (!$this->jalurSesdep($st)) {
            return $this->bolehKelola($st);
        }

        return $this->isAdmin()
            || $st->kontrol()
                ->where('tahap', SuratTugasKontrol::TAHAP_NOMOR)
                ->where('petugas_id', auth()->id())
                ->exists();
    }

    /** Dipakai UI: tombol tampil hanya kalau peran cocok dan ST siap terbit. */
    private function bolehTerbitkan(SuratTugas $st): bool
    {
        if (!$st->isDraf() || !$this->petugasTerbit($st)) {
            return false;
        }

        return !$this->jalurSesdep($st)
            || ($this->semuaTahapDisetujui($st) && (bool) $st->nomor_urut);
    }

    private function bolehUbahTusi(SuratTugas $st): bool
    {
        return $this->bolehKelola($st);
    }

    // ------------------------------------------------------------------

    public function nomorBerikutnya(Request $request)
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date'],
            'kode_klasifikasi' => ['required', 'string', 'max:50'],
        ]);

        return response()->json([
            'nomor' => $this->numbers->nomorBerikutnya(
                Carbon::parse($data['tanggal'])->year,
                $data['kode_klasifikasi']
            ),
        ]);
    }

    public function nomorMemoBerikutnya(Request $request)
    {
        $data = $request->validate(['tanggal' => ['required', 'date']]);

        return response()->json([
            'nomor' => $this->numbers->nomorMemoBerikutnya(Carbon::parse($data['tanggal'])->year),
        ]);
    }

    public function setCounter(Request $request): RedirectResponse
    {
        abort_unless($this->isAdmin(), 403);

        $data = $request->validate([
            'tahun' => ['required', 'integer', 'min:2020', 'max:2100'],
            'last_number' => ['required', 'integer', 'min:0'],
        ]);

        try {
            $this->numbers->setCounter((int) $data['tahun'], (int) $data['last_number']);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Counter nomor diperbarui. Nomor berikutnya: ' . ($data['last_number'] + 1));
    }

    public function data(SuratTugas $suratTugas)
    {
        abort_unless($suratTugas->nomor, 404);
        $suratTugas->load('peserta');

        $denganPegawai = $suratTugas->peserta->whereNotNull('pegawai_id');

        return response()->json([
            'id' => $suratTugas->id,
            'nomor' => $suratTugas->nomor,
            'uraian_kegiatan' => $suratTugas->untuk[0] ?? '',
            'tanggal_mulai' => $this->tgl($suratTugas->tanggal_mulai),
            'tanggal_selesai' => $this->tgl($suratTugas->tanggal_selesai),
            'tujuan' => trim((string) $suratTugas->provinsi) ?: null,
            'kota_tujuan' => trim((string) $suratTugas->kota) ?: null,
            'pegawai_ids' => $denganPegawai->pluck('pegawai_id')->map(fn($id) => (int) $id)->values(),
            'peserta_manual' => $suratTugas->peserta->whereNull('pegawai_id')->pluck('nama')->values(),
            'peserta_tanggal' => $denganPegawai->map(fn($p) => [
                'pegawai_id' => (int) $p->pegawai_id,
                'tanggal_mulai' => $this->tgl($p->tanggal_mulai),
                'tanggal_selesai' => $this->tgl($p->tanggal_mulai ? ($p->tanggal_selesai ?? $p->tanggal_mulai) : null),
            ])->values(),
        ]);
    }

    public function pdf(SuratTugas $suratTugas)
    {
        abort_unless($this->bolehLihat($suratTugas), 403);

        $suratTugas->load('peserta');

        $nama = 'Surat Tugas ' . str_replace('/', '-', $suratTugas->nomor ?? 'Draf ' . $suratTugas->id) . '.pdf';

        // Format template 2026 (opsional). Tanpa parameter = tampilan lama (standar).
        $format = request('format');
        if (in_array($format, ['lampiran', 'tanpa_lampiran', 'an_sesdep'], true)) {
            $suratTugas->load('peserta.pegawai');

            return Pdf::loadView('pdf.surat-tugas-template', ['st' => $suratTugas, 'format' => $format])
                ->setPaper('a4', 'portrait')
                ->stream($nama);
        }

        return Pdf::loadView('pdf.surat-tugas', ['st' => $suratTugas])
            ->setPaper('a4', 'portrait')
            ->stream($nama);
    }

    private function keShow(SuratTugas $st, string $pesan, string $tipe = 'success'): RedirectResponse
    {
        return redirect()->route('surat-tugas.show', $st)->with($tipe, $pesan);
    }

    private function kunciNomor(SuratTugas $st): SuratTugas
    {
        return $this->numbers->ambilNomor($st->id, $st->tanggal->format('Y-m-d'), $st->kode_klasifikasi);
    }

    private function tgl($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    private function dataForm(SuratTugas $st, array $peserta): array
    {
        return [
            'st' => $st,
            'peserta' => $peserta,
            'pegawaiOptions' => $this->pegawaiOptions(),
            'penandatanganOptions' => $this->penandatanganOptions(),
            'kabKotaMap' => $this->kabKotaMap(),
            // Pengambilan nomor otomatis di form hanya untuk admin (jalur lama).
            'bolehAmbilNomor' => $this->isAdmin(),
        ];
    }

    private function payload(SuratTugasRequest $request, ?SuratTugas $existing = null): array
    {
        $v = $request->validated();

        $dasar = array_merge(
            [self::DASAR_TETAP],
            collect($v['dasar'] ?? [])
                ->map(fn($d) => trim((string) $d))
                ->filter()
                ->reject(fn($d) => str_starts_with($d, 'Daftar Isian Pelaksanaan Anggaran'))
                ->values()->all()
        );

        $payload = [
            'tanggal' => $v['tanggal'],
            'tempat' => $v['tempat'],
            'kode_klasifikasi' => $existing?->nomor_urut ? $existing->kode_klasifikasi : $v['kode_klasifikasi'],
            'tanggal_mulai' => $v['tanggal_mulai'],
            'tanggal_selesai' => $v['tanggal_selesai'],
            'provinsi' => $v['provinsi'],
            'kota' => $v['kota'],
            'tujuan' => $v['tujuan'] ?? null,
            'menimbang' => $v['menimbang'] ?? null,
            'dasar' => $dasar,
            'untuk' => $v['untuk'],
            'kepada' => $v['kepada'] ?? config('surat_tugas.kepada_default'),
            'jabatan_penandatangan' => $v['jabatan_penandatangan'],
            'nama_penandatangan' => $v['nama_penandatangan'],
            'nip_penandatangan' => $v['nip_penandatangan'] ?? null,
        ];

        if (array_key_exists('nomor_memo', $v)) {
            $payload['nomor_memo'] = $v['nomor_memo'] ?: null;
        }

        return $payload;
    }

    private function pesertaUntukForm(SuratTugas $st): array
    {
        return $st->peserta->map(fn($p) => [
            'jenis' => $p->jenis ?: 'pns',
            'pegawai_id' => $p->pegawai_id,
            'nama' => $p->nama,
            'nip' => $p->nip,
            'jabatan' => $p->jabatan,
            'tanggal_mulai' => optional($p->tanggal_mulai)->format('Y-m-d') ?? '',
            'tanggal_selesai' => optional($p->tanggal_selesai)->format('Y-m-d') ?? '',
        ])->values()->all();
    }

    private function syncPeserta(SuratTugas $st, ?array $peserta): void
    {
        $st->peserta()->delete();

        [$pns, $nonPns] = collect($peserta ?? [])
            ->values()
            ->partition(fn($p) => ($p['jenis'] ?? 'pns') !== 'non_pns');

        foreach ($pns->concat($nonPns)->values() as $n => $p) {
            $mulai = $p['tanggal_mulai'] ?? null;
            $nama = trim((string) ($p['nama'] ?? ''));

            $pegawaiId = ($p['pegawai_id'] ?? null) ?: null;

            if (!$pegawaiId && $nama !== '') {
                $jenisStatus = ($p['jenis'] ?? 'pns') === 'non_pns' ? 'Non PNS' : 'PNS';

                $pegawai = Pegawai::where('nama', $nama)->first();

                if (!$pegawai) {
                    $pegawai = Pegawai::create([
                        'nama' => $nama,
                        'nip' => $p['nip'] ?? null,
                        'jabatan' => !empty($p['jabatan']) ? $p['jabatan'] : '-',
                        'status_kepegawaian' => $jenisStatus,
                    ]);
                }

                $pegawaiId = $pegawai->id;
            }

            $st->peserta()->create([
                'pegawai_id' => $pegawaiId,
                'jenis' => ($p['jenis'] ?? 'pns') === 'non_pns' ? 'non_pns' : 'pns',
                'urutan' => $n + 1,
                'nama' => $nama,
                'nip' => $p['nip'] ?? null,
                'jabatan' => $p['jabatan'] ?? null,
                'tanggal_mulai' => $mulai ?: null,
                'tanggal_selesai' => $mulai ? (($p['tanggal_selesai'] ?? null) ?: null) : null,
            ]);
        }
    }

    private function kabKotaMap(): array
    {
        return KabupatenKota::orderBy('nama')->get()
            ->groupBy('provinsi')
            ->map(fn($g) => $g->pluck('nama')->values()->all())
            ->sortKeys()
            ->all();
    }

    private function pegawaiOptions(): array
    {
        return Pegawai::query()
            ->orderBy('urutan')->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_gelar ?: $p->nama,
                'nip' => $p->nip ?? '',
                'jabatan' => $p->jabatan ?? '',
                'status' => $this->jenisPegawai($p->status_kepegawaian),
            ])->values()->all();
    }

    private function penandatanganOptions(): array
    {
        return Pegawai::query()
            ->whereNotNull('role_penandatangan')
            ->where('role_penandatangan', '!=', '')
            ->orderBy('urutan')->orderBy('nama')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->nama_gelar ?: $p->nama,
                'nip' => $p->nip ?? '',
                'jabatan' => $p->jabatan ?? '',
                'role' => $p->role_penandatangan,
            ])->values()->all();
    }

    private function jenisPegawai(?string $status): string
    {
        $s = strtolower(preg_replace('/[\s_\-]+/', '', (string) $status));

        return in_array($s, ['pns', 'cpns'], true) ? 'pns' : 'non_pns';
    }

    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }
}