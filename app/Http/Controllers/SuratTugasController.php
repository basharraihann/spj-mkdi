<?php

namespace App\Http\Controllers;

use App\Http\Requests\SuratTugasRequest;
use App\Models\Pegawai;
use App\Models\SuratTugas;
use App\Models\SuratTugasCounter;
use App\Services\SuratTugasNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuratTugasController extends Controller
{
    public function __construct(private SuratTugasNumberService $numbers)
    {
    }

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
        if ($st = $request->query('status')) {
            $q->where('status', $st);
        }

        $items = $q->paginate(15)->withQueryString();
        $tahun = now()->year;
        $counter = SuratTugasCounter::where('tahun', $tahun)->value('last_number') ?? 0;

        return view('surat-tugas.index', [
            'items' => $items,
            'tahun' => $tahun,
            'counter' => $counter,
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
            'dasar' => [''],
            'untuk' => array_merge([''], config('surat_tugas.untuk_default')),
        ]);

        return view('surat-tugas.create', [
            'st' => $st,
            'peserta' => [],
            'pegawaiOptions' => $this->pegawaiOptions(),
        ]);
    }

    public function store(SuratTugasRequest $request)
    {
        $st = DB::transaction(function () use ($request) {
            $st = SuratTugas::create($this->payload($request) + [
                'status' => 'draf',
                'created_by' => auth()->id(),
            ]);
            $this->syncPeserta($st, $request->validated('peserta'));
            return $st;
        });

        return redirect()->route('surat-tugas.show', $st)->with('success', 'Draf surat tugas dibuat.');
    }

    public function show(SuratTugas $suratTugas)
    {
        return view('surat-tugas.show', ['st' => $suratTugas->load('peserta')]);
    }

    public function edit(SuratTugas $suratTugas)
    {
        if (! $suratTugas->isDraf()) {
            return redirect()->route('surat-tugas.show', $suratTugas)
                ->with('error', 'ST yang sudah terbit dikunci dan tidak bisa diedit.');
        }

        return view('surat-tugas.edit', [
            'st' => $suratTugas,
            'peserta' => $suratTugas->peserta->map->only(['pegawai_id', 'nama', 'nip', 'jabatan'])->values()->all(),
            'pegawaiOptions' => $this->pegawaiOptions(),
        ]);
    }

    public function update(SuratTugasRequest $request, SuratTugas $suratTugas)
    {
        if (! $suratTugas->isDraf()) {
            return back()->with('error', 'ST yang sudah terbit dikunci.');
        }

        DB::transaction(function () use ($request, $suratTugas) {
            $suratTugas->update($this->payload($request));
            $this->syncPeserta($suratTugas, $request->validated('peserta'));
        });

        return redirect()->route('surat-tugas.show', $suratTugas)->with('success', 'Draf diperbarui.');
    }

    public function destroy(SuratTugas $suratTugas)
    {
        if (! $suratTugas->isDraf() || $suratTugas->nomor) {
            return back()->with('error', 'Hanya draf tanpa nomor yang bisa dihapus. ST bernomor cukup dibatalkan.');
        }
        $suratTugas->delete();

        return redirect()->route('surat-tugas.index')->with('success', 'Draf dihapus.');
    }

    public function terbitkan(SuratTugas $suratTugas)
    {
        if ($suratTugas->peserta()->count() < 1) {
            return back()->with('error', 'Tambahkan minimal satu peserta sebelum terbit.');
        }

        try {
            $st = $this->numbers->terbitkan($suratTugas->id);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('surat-tugas.show', $st)->with('success', 'ST terbit dengan nomor '.$st->nomor.'.');
    }

    public function selesai(SuratTugas $suratTugas)
    {
        if ($suratTugas->status !== 'terbit') {
            return back()->with('error', 'Hanya ST berstatus Terbit yang bisa ditandai Selesai.');
        }
        $suratTugas->update(['status' => 'selesai']);

        return back()->with('success', 'ST ditandai selesai.');
    }

    public function batal(Request $request, SuratTugas $suratTugas)
    {
        $data = $request->validate(['alasan_batal' => ['nullable', 'string', 'max:255']]);

        if (in_array($suratTugas->status, ['selesai', 'batal'], true)) {
            return back()->with('error', 'ST ini tidak bisa dibatalkan.');
        }

        // nomor tetap tercatat dan TIDAK dipakai ulang
        $suratTugas->update([
            'status' => 'batal',
            'dibatalkan_at' => now(),
            'alasan_batal' => $data['alasan_batal'] ?? null,
        ]);

        return back()->with('success', 'ST dibatalkan.');
    }

    public function setCounter(Request $request)
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

        return back()->with('success', 'Counter nomor diperbarui. Nomor berikutnya: '.($data['last_number'] + 1));
    }

    public function pdf(Request $request, SuratTugas $suratTugas)
    {
        $suratTugas->load('peserta');

        $pdf = Pdf::loadView('pdf.surat-tugas', ['st' => $suratTugas])->setPaper('a4', 'portrait');
        $nama = 'Surat Tugas '.str_replace('/', '-', $suratTugas->nomor ?? 'Draf '.$suratTugas->id).'.pdf';

        return $request->boolean('download') ? $pdf->download($nama) : $pdf->stream($nama);
    }

    /* ---------------- helper ---------------- */

    private function payload(SuratTugasRequest $request): array
    {
        $v = $request->validated();

        return [
            'tanggal' => $v['tanggal'],
            'tempat' => $v['tempat'],
            'kode_klasifikasi' => $v['kode_klasifikasi'],
            'menimbang' => $v['menimbang'] ?? null,
            'dasar' => $v['dasar'] ?? [],
            'untuk' => $v['untuk'],
            'kepada' => $v['kepada'] ?? config('surat_tugas.kepada_default'),
            'jabatan_penandatangan' => $v['jabatan_penandatangan'],
            'nama_penandatangan' => $v['nama_penandatangan'],
            'nip_penandatangan' => $v['nip_penandatangan'] ?? null,
        ];
    }

    private function syncPeserta(SuratTugas $st, array $peserta): void
    {
        $st->peserta()->delete();
        foreach (array_values($peserta) as $i => $p) {
            $st->peserta()->create([
                'pegawai_id' => $p['pegawai_id'] ?? null,
                'urutan' => $i + 1,
                'nama' => $p['nama'],
                'nip' => $p['nip'] ?? null,
                'jabatan' => $p['jabatan'] ?? null,
            ]);
        }
    }

    /** Data pegawai (read-only) untuk dropdown. Sesuaikan nama kolom jika berbeda. */
    private function pegawaiOptions(): array
    {
        return Pegawai::query()->get()->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->nama ?? $p->name ?? '',
            'nip' => $p->nip ?? '',
            'jabatan' => $p->jabatan ?? '',
        ])->sortBy('nama')->values()->all();
    }

    private function isAdmin(): bool
    {
        return (bool) auth()->user()?->isAdmin();
    }
}