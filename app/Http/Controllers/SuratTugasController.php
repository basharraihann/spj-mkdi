<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratTugasRequest;
use App\Models\KabupatenKota;
use App\Models\Pegawai;
use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use App\Services\SuratTugasNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratTugasController extends Controller
{
    /** Dasar nomor 1: tetap, tidak bisa diubah dari form. */
    private const DASAR_TETAP = 'Daftar Isian Pelaksanaan Anggaran (DIPA) Satuan Kerja Kementerian Koordinator Bidang Pangan Tahun 2025 Nomor: SP DIPA-131.01-0/2026 tanggal 1 Desember 2025';

    public function __construct(private SuratTugasNumberService $numbers)
    {
    }

    /* =====================================================================
     |  CRUD
     * ===================================================================== */

    public function index(Request $request)
    {
        $q = SuratTugas::withCount('peserta')->latest('tanggal')->latest('id');

        if ($s = $request->query('q')) {
            $q->where(function ($w) use ($s) {
                $w->where('nomor', 'like', "%{$s}%")
                    ->orWhere('untuk', 'like', "%{$s}%")
                    ->orWhere('menimbang', 'like', "%{$s}%");
            });
        }

        $tahun = now()->year;

        return view('surat-tugas.index', [
            'items' => $q->paginate(15)->withQueryString(),
            'tahun' => $tahun,
            'counter' => SuratTugasCounter::where('tahun', $tahun)->value('last_number') ?? 0,
            'nomorBerikutnya' => $this->numbers->nomorBerikutnya($tahun, config('surat_tugas.kode_klasifikasi')),
            'isAdmin' => $this->isAdmin(),
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
            ]);
            $this->syncPeserta($st, $request->validated('peserta'));

            return $st;
        });

        // nomor baru dikunci saat Simpan
        if ($request->boolean('minta_nomor')) {
            $st = $this->kunciNomor($st);

            return $this->keShow($st, 'ST disimpan dengan nomor ' . $st->nomor . '.');
        }

        return $this->keShow($st, 'Draf surat tugas dibuat.');
    }

    public function show(SuratTugas $suratTugas)
    {
        return view('surat-tugas.show', ['st' => $suratTugas->load('peserta')]);
    }

    public function edit(SuratTugas $suratTugas)
    {
        if (!$suratTugas->isDraf()) {
            return $this->keShow($suratTugas, 'ST ini dikunci dan tidak bisa diedit.', 'error');
        }

        return view('surat-tugas.edit', $this->dataForm($suratTugas, $this->pesertaUntukForm($suratTugas)));
    }

    public function update(SuratTugasRequest $request, SuratTugas $suratTugas): RedirectResponse
    {
        if (!$suratTugas->isDraf()) {
            return back()->with('error', 'ST ini dikunci.');
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

        if ($request->boolean('minta_nomor') && !$suratTugas->nomor_urut) {
            $suratTugas = $this->kunciNomor($suratTugas->refresh());

            return $this->keShow($suratTugas, 'ST disimpan dengan nomor ' . $suratTugas->nomor . '.');
        }

        return $this->keShow($suratTugas, 'Surat tugas diperbarui.');
    }

    public function destroy(SuratTugas $suratTugas): RedirectResponse
    {
        DB::transaction(function () use ($suratTugas) {
            $suratTugas->peserta()->delete();
            $suratTugas->delete();
        });

        return redirect()->route('surat-tugas.index')->with('success', 'Surat tugas dihapus.');
    }

    /* =====================================================================
     |  Nomor
     * ===================================================================== */

    /** Tombol "Ambil Nomor": hanya menampilkan nomor berikutnya, tidak menyimpan dan tidak mengubah counter. */
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

    /** Tombol "Ambil Nomor Memo": pratinjau saja, tidak menyimpan. */
    public function nomorMemoBerikutnya(Request $request)
    {
        $data = $request->validate(['tanggal' => ['required', 'date']]);

        return response()->json([
            'nomor' => $this->numbers->nomorMemoBerikutnya(Carbon::parse($data['tanggal'])->year),
        ]);
    }

    public function setCounter(Request $request): RedirectResponse
    {
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

    /* =====================================================================
     |  Data untuk form agenda & PDF
     * ===================================================================== */

    /** Data ST untuk mengisi form agenda. Hanya ST yang sudah bernomor. */
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
            'tujuan' => trim((string) $suratTugas->provinsi) ?: null,   // di agenda, "tujuan" = provinsi
            'kota_tujuan' => trim((string) $suratTugas->kota) ?: null,
            'pegawai_ids' => $denganPegawai->pluck('pegawai_id')->map(fn($id) => (int) $id)->values(),
            'peserta_manual' => $suratTugas->peserta->whereNull('pegawai_id')->pluck('nama')->values(),
            // tanggal tugas per orang (null = ikut rentang ST)
            'peserta_tanggal' => $denganPegawai->map(fn($p) => [
                'pegawai_id' => (int) $p->pegawai_id,
                'tanggal_mulai' => $this->tgl($p->tanggal_mulai),
                'tanggal_selesai' => $this->tgl($p->tanggal_mulai ? ($p->tanggal_selesai ?? $p->tanggal_mulai) : null),
            ])->values(),
        ]);
    }

    public function pdf(SuratTugas $suratTugas)
    {
        $suratTugas->load('peserta');

        $nama = 'Surat Tugas ' . str_replace('/', '-', $suratTugas->nomor ?? 'Draf ' . $suratTugas->id) . '.pdf';

        return Pdf::loadView('pdf.surat-tugas', ['st' => $suratTugas])
            ->setPaper('a4', 'portrait')
            ->stream($nama);
    }

    /* =====================================================================
     |  Helper
     * ===================================================================== */

    /** Redirect ke halaman detail + notifikasi global (tipe: success | error | warning | info). */
    private function keShow(SuratTugas $st, string $pesan, string $tipe = 'success'): RedirectResponse
    {
        return redirect()->route('surat-tugas.show', $st)->with($tipe, $pesan);
    }

    /** Ambil dan kunci nomor ST dari counter. */
    private function kunciNomor(SuratTugas $st): SuratTugas
    {
        return $this->numbers->ambilNomor($st->id, $st->tanggal->format('Y-m-d'), $st->kode_klasifikasi);
    }

    private function tgl($value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    /** Data umum untuk view create & edit. */
    private function dataForm(SuratTugas $st, array $peserta): array
    {
        return [
            'st' => $st,
            'peserta' => $peserta,
            'pegawaiOptions' => $this->pegawaiOptions(),
            'penandatanganOptions' => $this->penandatanganOptions(),
            'kabKotaMap' => $this->kabKotaMap(),
        ];
    }

    private function payload(SuratTugasRequest $request, ?SuratTugas $existing = null): array
    {
        $v = $request->validated();

        // dasar 1 dipaksa dari server; baris kosong dan duplikat dasar tetap dibuang
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
            // kode dikunci setelah nomor diambil
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

        // nomor_memo hanya disentuh kalau sudah masuk aturan validasi (supaya tidak menimpa dengan null)
        if (array_key_exists('nomor_memo', $v)) {
            $payload['nomor_memo'] = $v['nomor_memo'] ?: null;
        }

        return $payload;
    }

    /** Peserta ST dalam bentuk array untuk form edit. */
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

    /** Simpan peserta: PNS dulu, lalu Non PNS (urutan input dipertahankan di tiap kelompok). */
    private function syncPeserta(SuratTugas $st, ?array $peserta): void
    {
        $st->peserta()->delete();

        [$pns, $nonPns] = collect($peserta ?? [])
            ->values()
            ->partition(fn($p) => ($p['jenis'] ?? 'pns') !== 'non_pns');

        foreach ($pns->concat($nonPns)->values() as $n => $p) {
            $mulai = $p['tanggal_mulai'] ?? null;

            $st->peserta()->create([
                'pegawai_id' => ($p['pegawai_id'] ?? null) ?: null,
                'jenis' => ($p['jenis'] ?? 'pns') === 'non_pns' ? 'non_pns' : 'pns',
                'urutan' => $n + 1,
                'nama' => $p['nama'],
                'nip' => $p['nip'] ?? null,
                'jabatan' => $p['jabatan'] ?? null,
                'tanggal_mulai' => $mulai ?: null,
                'tanggal_selesai' => $mulai ? (($p['tanggal_selesai'] ?? null) ?: null) : null,
            ]);
        }
    }

    /** ['Bali' => ['Kota Denpasar', ...], ...] untuk dropdown provinsi -> kab/kota. */
    private function kabKotaMap(): array
    {
        return KabupatenKota::orderBy('nama')->get()
            ->groupBy('provinsi')
            ->map(fn($g) => $g->pluck('nama')->values()->all())
            ->sortKeys()
            ->all();
    }

    /** Pegawai untuk dropdown peserta, dipisah PNS / Non PNS dari status_kepegawaian. */
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

    /** Hanya pegawai yang role_penandatangan-nya terisi. */
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

    /** 'PNS' / 'CPNS' -> pns. Selain itu (Non PNS, PPPK, kosong) -> non_pns. */
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