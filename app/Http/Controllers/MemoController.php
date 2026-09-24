<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
use App\Models\Pegawai;
use App\Services\NomorMemoService;
use Illuminate\Http\Request;

class MemoController extends Controller
{
    /**
     * Daftar semua nomor memo, gabungan dari dua sumber:
     * - agendas.nomor_memo_pns / nomor_memo_non_pns (nomor yang nempel ke agenda,
     *   1 agenda bisa nyumbang 1 atau 2 baris)
     * - memo_entries (nomor yang dibuat mandiri lewat halaman "Buat Nomor Memo",
     *   gak nempel ke agenda manapun)
     */
    public function index()
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
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_pns,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->biaya_asn,
                    'status' => 'PNS',
                ]);
            }

            if (!empty($agenda->nomor_memo_non_pns)) {
                $memos->push([
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_non_pns,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->biaya_non_asn,
                    'status' => 'Non PNS',
                ]);
            }
        }

        foreach (MemoEntry::with('pic')->get() as $entry) {
            $memos->push([
                'agenda_id' => null,
                'nomor_memo' => $entry->nomor_memo,
                'tanggal_memo' => $entry->tanggal_memo,
                'kode_giat' => null,
                'uraian_kegiatan' => $entry->uraian_kegiatan,
                'pic' => $entry->pic->nama_gelar ?? $entry->pic->nama ?? '-',
                'mak' => $entry->mak,
                'nominal' => $entry->nominal,
                'status' => null,
                'mandiri' => true,
            ]);
        }

        $memos = $memos->sortByDesc('tanggal_memo')->values();

        $nomorBerikutnya = NomorMemoService::dataBerikutnya();

        return view('memo.index', compact('memos', 'nomorBerikutnya'));
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

        return view('memo.create', compact('pegawaiList', 'nomorBerikutnya'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_memo' => 'required|string',
            'tanggal_memo' => 'required|date',
            'uraian_kegiatan' => 'nullable|string',
            'pic_id' => 'nullable|exists:pegawai,id',
            'mak' => 'nullable|string',
            'nominal' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $validated['nomor_urut'] = NomorMemoService::ekstrakUrutan($validated['nomor_memo']) ?? 0;

        MemoEntry::create($validated);

        return redirect()->route('memo.index')
            ->with('success', 'Nomor memo berhasil dibuat.');
    }
}