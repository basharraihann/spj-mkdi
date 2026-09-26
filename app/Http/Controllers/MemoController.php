<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;
use App\Models\Pegawai;
use App\Services\NomorMemoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    /**
     * Daftar semua nomor memo, gabungan dari dua sumber:
     * - agendas.nomor_memo_pns / nomor_memo_non_pns (nomor yang nempel ke agenda,
     *   1 agenda bisa nyumbang 1 atau 2 baris) -> jenis "perdin"
     * - memo_entries (nomor yang dibuat mandiri lewat halaman "Buat Nomor Memo",
     *   gak nempel ke agenda manapun) -> jenis "konsumsi" / "honorarium"
     *
     * Urutan ditentukan dari nomor_urut (angka di depan nomor memo), terbesar
     * di atas. Mendukung filter lewat query string: jenis, pic, cari,
     * dari_tanggal, sampai_tanggal.
     */
    public function index(Request $request)
    {
        $agendas = Agenda::with(['penanggungJawab', 'pic'])
            ->latest()
            ->get();

        $memos = collect();

        foreach ($agendas as $agenda) {
            $pic = $agenda->pic->nama_gelar
                ?? $agenda->pic->nama
                ?? $agenda->penanggungJawab->nama_gelar
                ?? $agenda->penanggungJawab->nama
                ?? '-';

            if (!empty($agenda->nomor_memo_pns)) {
                $memos->push([
                    'id' => 'agenda-' . $agenda->id . '-pns',
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_pns,
                    'nomor_urut' => NomorMemoService::ekstrakUrutan($agenda->nomor_memo_pns) ?? 0,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->biaya_asn,
                    'status' => 'PNS',
                    'jenis' => 'perdin',
                    'jenis_label' => 'Perjalanan Dinas',
                    'editable' => false,
                    'pdf_url' => route('agendas.memorandum.pdf', ['agenda' => $agenda->id, 'status' => 'pns']),
                ]);
            }

            if (!empty($agenda->nomor_memo_non_pns)) {
                $memos->push([
                    'id' => 'agenda-' . $agenda->id . '-non_pns',
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_non_pns,
                    'nomor_urut' => NomorMemoService::ekstrakUrutan($agenda->nomor_memo_non_pns) ?? 0,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->biaya_non_asn,
                    'status' => 'Non PNS',
                    'jenis' => 'perdin',
                    'jenis_label' => 'Perjalanan Dinas',
                    'editable' => false,
                    'pdf_url' => route('agendas.memorandum.pdf', ['agenda' => $agenda->id, 'status' => 'non-pns']),
                ]);
            }
        }

        foreach (MemoEntry::with('pic')->get() as $entry) {
            $memos->push([
                'id' => 'entry-' . $entry->id,
                'agenda_id' => null,
                'nomor_memo' => $entry->nomor_memo,
                'nomor_urut' => $entry->nomor_urut ?? NomorMemoService::ekstrakUrutan($entry->nomor_memo) ?? 0,
                'tanggal_memo' => $entry->tanggal_memo,
                'kode_giat' => null,
                'uraian_kegiatan' => $entry->uraian_kegiatan,
                'pic' => $entry->pic->nama_gelar ?? $entry->pic->nama ?? '-',
                'mak' => $entry->mak,
                'nominal' => $entry->nominal,
                'status' => null,
                'mandiri' => true,
                'jenis' => $entry->jenis_memo,
                'jenis_label' => $entry->jenis_memo === 'konsumsi' ? 'Konsumsi' : 'Honorarium',
                'editable' => true,
                'edit_url' => route('memo.edit', $entry->id),
                'pdf_url' => route('memo.pdf', $entry->id),
            ]);
        }

        // Opsi PIC buat dropdown filter, diambil dari data yang benar-benar ada
        $picOptions = $memos->pluck('pic')->unique()->sort()->values();

        // Urutan: nomor memo terbesar (terbaru) di atas
        $memos = $memos->sortByDesc('nomor_urut')->values();

        $nomorBerikutnya = NomorMemoService::dataBerikutnya();

        return view('memo.index', compact('memos', 'nomorBerikutnya', 'picOptions'));
    }

    /**
     * Endpoint AJAX buat tombol "Ambil Nomor Memo".
     */
    public function nomorBerikutnya()
    {
        return response()->json(NomorMemoService::dataBerikutnya());
    }

    /**
     * Form "Buat Nomor Memo" mandiri — bikin nomor memo tanpa harus bikin agenda.
     */
    public function create()
    {
        $pegawaiList = Pegawai::orderBy('nama')->get();
        $nomorBerikutnya = NomorMemoService::dataBerikutnya();
        $makOptions = MakOption::orderBy('mak')->get();

        return view('memo.create', compact('pegawaiList', 'nomorBerikutnya', 'makOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_memo' => 'required|string',
            'jenis_memo' => 'required|in:konsumsi,honorarium',
            'tanggal_memo' => 'required|date',
            'uraian_kegiatan' => 'required|string',
            'pic_id' => 'required|exists:pegawai,id',
            'ppk_id' => 'required|exists:pegawai,id',
            'bendahara_id' => 'required|exists:pegawai,id',
            'penanggung_jawab_id' => 'required|exists:pegawai,id',
            'petugas_verifikasi_id' => 'required|exists:pegawai,id',
            'mak' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['nomor_urut'] = NomorMemoService::ekstrakUrutan($validated['nomor_memo']) ?? 0;

        MemoEntry::create($validated);

        return redirect()->route('memo.index')
            ->with('success', 'Nomor memo berhasil dibuat.');
    }

    /**
     * Form edit memo mandiri. Hanya berlaku buat memo_entries — nomor memo yang
     * nempel ke agenda diedit lewat halaman agenda-nya sendiri.
     */
    public function edit(MemoEntry $memo)
    {
        $pegawaiList = Pegawai::orderBy('nama')->get();
        $makOptions = MakOption::orderBy('mak')->get();

        return view('memo.edit', compact('memo', 'pegawaiList', 'makOptions'));
    }

    public function update(Request $request, MemoEntry $memo)
    {
        $validated = $request->validate([
            'nomor_memo' => 'required|string',
            'jenis_memo' => 'required|in:konsumsi,honorarium',
            'tanggal_memo' => 'required|date',
            'uraian_kegiatan' => 'required|string',
            'pic_id' => 'required|exists:pegawai,id',
            'ppk_id' => 'required|exists:pegawai,id',
            'bendahara_id' => 'required|exists:pegawai,id',
            'penanggung_jawab_id' => 'required|exists:pegawai,id',
            'petugas_verifikasi_id' => 'required|exists:pegawai,id',
            'mak' => 'required|string',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['nomor_urut'] = NomorMemoService::ekstrakUrutan($validated['nomor_memo']) ?? 0;

        $memo->update($validated);

        return redirect()->route('memo.index')
            ->with('success', 'Nomor memo berhasil diperbarui.');
    }

    /**
     * Hapus nomor memo. $id di sini bukan primary key murni, tapi id komposit
     * yang dibentuk di index():
     * - "agenda-{agenda_id}-pns"     -> kosongkan kolom nomor_memo_pns di agenda
     * - "agenda-{agenda_id}-non_pns" -> kosongkan kolom nomor_memo_non_pns di agenda
     * - "entry-{memo_entry_id}"      -> hapus baris di memo_entries
     */
    public function destroy(string $id)
    {
        if (str_starts_with($id, 'agenda-')) {
            $parts = explode('-', $id, 3);
            $agendaId = $parts[1] ?? null;
            $tipe = $parts[2] ?? null;

            $agenda = Agenda::findOrFail($agendaId);

            if ($tipe === 'pns') {
                $agenda->update(['nomor_memo_pns' => null]);
            } elseif ($tipe === 'non_pns') {
                $agenda->update(['nomor_memo_non_pns' => null]);
            } else {
                abort(404);
            }

            return redirect()->route('memo.index')
                ->with('success', 'Nomor memo pada agenda berhasil dikosongkan.');
        }

        if (str_starts_with($id, 'entry-')) {
            $entryId = substr($id, strlen('entry-'));

            $entry = MemoEntry::findOrFail($entryId);
            $entry->delete();

            return redirect()->route('memo.index')
                ->with('success', 'Nomor memo berhasil dihapus.');
        }

        abort(404);
    }

    public function pdf(MemoEntry $memo)
    {
        $memo->load(['pic', 'ppk', 'bendahara', 'penanggungJawab', 'petugasVerifikasi']);

        $makOption = MakOption::where('mak', $memo->mak)->first();
        $kodeParts = explode('.', $memo->mak ?? '');
        $kodeGiat = count($kodeParts) === 6 ? implode('.', array_slice($kodeParts, 0, 3)) : null;
        $kodeKomponen = $kodeParts[3] ?? null;
        $kodeAkunAp = $kodeParts[4] ?? null;
        $kodeBelanja = $kodeParts[5] ?? null;

        $agendaProxy = (object) [
            'dari_memo' => 'Pejabat Pembuat Komitmen Biro Manajemen Kinerja, Data dan Informasi',
            'mak' => $memo->mak,
            'kode_giat' => $kodeGiat,
            'uraian_giat' => $makOption->uraian_giat ?? null,
            'kode_komponen' => $kodeKomponen,
            'uraian_komponen' => $makOption->uraian_komponen ?? null,
            'kode_akun_ap' => $kodeAkunAp,
            'uraian_akun_ap' => $makOption->uraian_akun_ap ?? null,
            'kode_belanja' => $kodeBelanja,
            'uraian_belanja' => $makOption->uraian_belanja ?? $memo->uraian_kegiatan,
            'ppk' => $memo->ppk,
            'bendahara' => $memo->bendahara,
            'penanggungJawab' => $memo->penanggungJawab,
            'petugasVerifikasi' => $memo->petugasVerifikasi,
        ];

        $pdf = Pdf::loadView('pdf.memorandum', [
            'agenda' => $agendaProxy,
            'nomorMemo' => $memo->nomor_memo,
            'uraianMemo' => $memo->uraian_kegiatan,
            'totalBiaya' => $memo->nominal,
            'rincianBiaya' => [],
            'terbilang' => null,
        ])->setPaper('a4', 'portrait');

        $namaFile = 'Memorandum-' . str_replace(['/', '\\'], '-', $memo->nomor_memo) . '.pdf';

        return $pdf->stream($namaFile);
    }
}