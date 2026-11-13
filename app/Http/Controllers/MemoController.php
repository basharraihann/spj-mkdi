<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MakOption;
use App\Models\MemoEntry;
use App\Models\Pegawai;
use App\Models\Unit;
use App\Services\MakSisaService;
use App\Services\MemoTahapService;
use App\Services\NomorMemoService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class MemoController extends Controller
{
    /**
     * Daftar semua nomor memo, gabungan dari dua sumber:
     * - agendas.nomor_memo_pns / nomor_memo_non_pns -> jenis "perdin"
     * - memo_entries (dibuat mandiri) -> jenis sesuai MemoEntry::JENIS
     *   (konsumsi, honorarium, atk, seminar_kit, sewa_ruangan)
     *
     * Ditampilkan satu baris per agenda: memo PNS dan Non PNS dari agenda yang
     * sama digabung. Memo mandiri tetap satu grup satu item.
     *
     * Daftar ini tetap dibatasi per unit (global scope 'unit'). Hanya
     * penomoran yang lintas unit (lihat NomorMemoService).
     */
    public function index(Request $request)
    {
        // Keuangan (TU) membaca lintas unit; unit kerja tetap dibatasi scope 'unit'.
        $lintasUnit = (bool) auth()->user()?->can('kelola-anggaran');

        // Label unit pengirim (kolom kode/nama: sesuaikan dengan tabel units).
        $unitMap = Unit::all()->mapWithKeys(
            fn($u) => [$u->id => trim(($u->kode ?? '') . ' ' . ($u->nama ?? ''))]
        );

        $agendas = Agenda::query()
            ->when($lintasUnit, fn($q) => $q->withoutGlobalScope('unit'))
            ->with(['penanggungJawab', 'pic'])
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
                    'unit' => ($unitMap[$agenda->unit_id] ?? '') ?: '-',
                    'group_key' => 'agenda-' . $agenda->id,
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_pns,
                    'nomor_urut' => NomorMemoService::ekstrakUrutan($agenda->nomor_memo_pns) ?? 0,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->nominal_memo_pns ?? $agenda->biaya_asn,
                    'status' => 'PNS',
                    'status_anggaran' => $agenda->status_pns ?? 'outstanding',
                    'tgl_sakti' => $agenda->tgl_sakti_pns,
                    'tgl_sp2d' => $agenda->tgl_sp2d_pns,
                    'jenis' => 'perdin',
                    'jenis_label' => 'Perjalanan Dinas',
                    'editable' => false,
                    'pdf_url' => route('agendas.memorandum.pdf', ['agenda' => $agenda->id, 'status' => 'pns']),
                ]);
            }

            if (!empty($agenda->nomor_memo_non_pns)) {
                $memos->push([
                    'id' => 'agenda-' . $agenda->id . '-non_pns',
                    'unit' => ($unitMap[$agenda->unit_id] ?? '') ?: '-',
                    'group_key' => 'agenda-' . $agenda->id,
                    'agenda_id' => $agenda->id,
                    'nomor_memo' => $agenda->nomor_memo_non_pns,
                    'nomor_urut' => NomorMemoService::ekstrakUrutan($agenda->nomor_memo_non_pns) ?? 0,
                    'tanggal_memo' => $agenda->created_at,
                    'kode_giat' => $agenda->kode_giat,
                    'uraian_kegiatan' => $agenda->uraian_kegiatan,
                    'pic' => $pic,
                    'mak' => $agenda->mak,
                    'nominal' => $agenda->nominal_memo_non_pns ?? $agenda->biaya_non_asn,
                    'status' => 'Non PNS',
                    'status_anggaran' => $agenda->status_non_pns ?? 'outstanding',
                    'tgl_sakti' => $agenda->tgl_sakti_non_pns,
                    'tgl_sp2d' => $agenda->tgl_sp2d_non_pns,
                    'jenis' => 'perdin',
                    'jenis_label' => 'Perjalanan Dinas',
                    'editable' => false,
                    'pdf_url' => route('agendas.memorandum.pdf', ['agenda' => $agenda->id, 'status' => 'non-pns']),
                ]);
            }
        }

        $entries = MemoEntry::query()
            ->when($lintasUnit, fn($q) => $q->withoutGlobalScope('unit'))
            ->with('pic')
            ->get();

        foreach ($entries as $entry) {
            $memos->push([
                'id' => 'entry-' . $entry->id,
                'unit' => ($unitMap[$entry->unit_id] ?? '') ?: '-',
                'group_key' => 'entry-' . $entry->id,
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
                'status_anggaran' => $entry->status_anggaran ?? 'outstanding',
                'tgl_sakti' => $entry->tgl_sakti,
                'tgl_sp2d' => $entry->tgl_sp2d,
                'mandiri' => true,
                'jenis' => $entry->jenis_memo,
                'jenis_label' => $entry->jenis_label, // accessor di model MemoEntry
                'editable' => ($entry->status_anggaran ?? 'outstanding') === 'outstanding',
                'edit_url' => route('memo.edit', $entry->id),
                'pdf_url' => route('memo.pdf', $entry->id),
            ]);
        }

        // Tahap alur tiap memo, dimuat sekali untuk semua memo (tanpa N+1).
        $tahapMap = MemoTahapService::petaTahap(
            $memos->mapWithKeys(fn($m) => [$m['id'] => $m['status_anggaran']])->all()
        );

        // Pengajuan pengembalian yang masih menunggu, satu query untuk semua memo.
        $pengajuanMenunggu = DB::table('memo_pengajuan_kembali')
            ->where('status', 'menunggu')
            ->whereIn('memo_key', $memos->pluck('id')->all())
            ->get()
            ->keyBy('memo_key');

        // Id milik unit user sendiri (scope 'unit' aktif di sini). Dipakai Keuangan untuk
        // menyembunyikan draft unit lain dan mematikan tombol aksi unit kerja di baris unit lain.
        $agendaSendiri = $lintasUnit ? Agenda::pluck('id')->all() : [];
        $entrySendiri = $lintasUnit ? MemoEntry::pluck('id')->all() : [];

        $memos = $memos->map(function ($m) use ($tahapMap, $pengajuanMenunggu, $lintasUnit, $agendaSendiri, $entrySendiri) {
            $m['tahap'] = $tahapMap[$m['id']];

            $milik = !$lintasUnit
                || (!empty($m['mandiri'])
                    ? in_array((int) substr($m['id'], 6), $entrySendiri, true) // 'entry-' = 6 huruf
                    : in_array($m['agenda_id'], $agendaSendiri, true));
            $m['milik_sendiri'] = $milik;
            $bisaDiedit = in_array($m['tahap'], ['draft', 'dikembalikan'], true);

            $p = $pengajuanMenunggu->get($m['id']);
            $m['pengajuan_id'] = $p->id ?? null;
            $m['pengajuan_alasan'] = $p->alasan ?? null;

            if (!empty($m['mandiri'])) {
                $m['editable'] = $milik && $m['status_anggaran'] === 'outstanding' && $bisaDiedit;
            }
            $m['bisa_kirim'] = $milik && $bisaDiedit && $m['status_anggaran'] === 'outstanding';
            $m['bisa_ajukan'] = $milik && $p === null
                && in_array($m['tahap'], ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk'], true);

            // Draft / dikembalikan milik unit lain belum "dikirim ke Keuangan": jangan tampil.
            $m['sembunyi'] = !$milik && $bisaDiedit;

            return $m;
        })->reject(fn($m) => $m['sembunyi'])->values();

        // Riwayat tiap memo, dimuat sekali untuk semua memo yang tampil (tanpa N+1).
        $logMap = $this->petaLog($memos->pluck('id')->all());

        // Opsi PIC buat dropdown filter
        $picOptions = $memos->pluck('pic')->unique()->sort()->values();

        // Urutan: nomor memo terbesar (terbaru) di atas
        $memos = $memos->sortByDesc('nomor_urut')->values();

        // Satu grup = satu agenda (atau satu memo mandiri).
        $groups = $memos
            ->groupBy('group_key')
            ->map(function ($items) {
                $items = $items
                    ->sortBy(fn($i) => $i['status'] === 'Non PNS' ? 1 : 0) // PNS di atas
                    ->values();
                $first = $items->first();

                return [
                    'agenda_id' => $first['agenda_id'],
                    'delete_id' => $first['id'],
                    'jenis' => $first['jenis'],
                    'jenis_label' => $first['jenis_label'],
                    'tanggal_memo' => $first['tanggal_memo'],
                    'uraian_kegiatan' => $first['uraian_kegiatan'],
                    'pic' => $first['pic'],
                    'mak' => $first['mak'],
                    'unit' => $first['unit'],
                    'items' => $items->all(),
                ];
            })
            ->values();

        $nomorBerikutnya = NomorMemoService::dataBerikutnya();

        return view('memo.index', compact('groups', 'nomorBerikutnya', 'picOptions', 'logMap'));
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
        $makOutstanding = MakSisaService::outstandingDetail();
        $makSisaEfektif = MakSisaService::sisaEfektif();

        return view('memo.create', compact(
            'pegawaiList',
            'nomorBerikutnya',
            'makOptions',
            'makOutstanding',
            'makSisaEfektif'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(self::rules());

        $validated['nomor_urut'] = NomorMemoService::ekstrakUrutan($validated['nomor_memo']) ?? 0;

        DB::transaction(function () use ($validated) {
            // Kunci baris MAK: request bersamaan untuk MAK yang sama antre di sini,
            // jadi dua unit tidak bisa sama-sama lolos dari sisa yang sama.
            MakOption::where('mak', $validated['mak'])->lockForUpdate()->first();

            // Nomor itu satu urutan untuk semua unit: cek lintas unit.
            $this->pastikanNomorBelumDipakai($validated['nomor_memo']);

            // Sisa efektif dihitung lintas unit (outstanding semua unit ikut dikurangi).
            if ($pesan = MakSisaService::cekNominal($validated['mak'], (int) $validated['nominal'])) {
                throw ValidationException::withMessages(['nominal' => $pesan]);
            }

            // status_anggaran tidak diisi di sini: default kolomnya 'outstanding'.
            MemoEntry::create($validated);
        });

        return redirect()->route('memo.index')
            ->with('success', 'Nomor memo berhasil dibuat.');
    }

    /**
     * Form edit memo mandiri. Hanya berlaku buat memo_entries — nomor memo yang
     * nempel ke agenda diedit lewat halaman agenda-nya sendiri.
     */
    public function edit(MemoEntry $memo)
    {
        abort_if(
            !$this->bolehEdit($memo),
            403,
            'Memo sudah dikirim, SAKTI, atau SP2D, tidak bisa diubah. Ajukan pengembalian jika perlu perbaikan.'
        );

        $pegawaiList = Pegawai::orderBy('nama')->get();
        $makOptions = MakOption::orderBy('mak')->get();
        // Nominal memo ini sendiri tidak ikut dihitung.
        $makOutstanding = MakSisaService::outstandingDetail($memo->id);
        $makSisaEfektif = MakSisaService::sisaEfektif($memo->id);

        return view('memo.edit', compact(
            'memo',
            'pegawaiList',
            'makOptions',
            'makOutstanding',
            'makSisaEfektif'
        ));
    }

    public function update(Request $request, MemoEntry $memo)
    {
        abort_if(
            !$this->bolehEdit($memo),
            403,
            'Memo sudah dikirim, SAKTI, atau SP2D, tidak bisa diubah. Ajukan pengembalian jika perlu perbaikan.'
        );

        $validated = $request->validate(self::rules());

        $validated['nomor_urut'] = NomorMemoService::ekstrakUrutan($validated['nomor_memo']) ?? 0;

        DB::transaction(function () use ($validated, $memo) {
            MakOption::where('mak', $validated['mak'])->lockForUpdate()->first();

            // Abaikan memo yang sedang diedit supaya nomornya sendiri tidak dianggap bentrok.
            $this->pastikanNomorBelumDipakai($validated['nomor_memo'], $memo->id);

            // Nominal memo ini sendiri dikecualikan dari outstanding.
            if ($pesan = MakSisaService::cekNominal($validated['mak'], (int) $validated['nominal'], $memo->id)) {
                throw ValidationException::withMessages(['nominal' => $pesan]);
            }

            $memo->update($validated);
        });

        return redirect()->route('memo.index')
            ->with('success', 'Nomor memo berhasil diperbarui.');
    }

    /**
     * Hapus nomor memo. $id di sini bukan primary key murni, tapi id komposit
     * yang dibentuk di index():
     * - "agenda-{agenda_id}-pns" / "agenda-{agenda_id}-non_pns" -> hapus agenda
     *   (memo PNS dan Non PNS yang nempel ikut hilang)
     * - "entry-{memo_entry_id}" -> hapus baris di memo_entries
     *
     * Memo yang sudah SAKTI/SP2D tidak boleh dihapus (uangnya sudah tercatat di SAKTI);
     * ubah statusnya jadi Batal dulu lewat dropdown status.
     */
    public function destroy(string $id)
    {
        if (str_starts_with($id, 'agenda-')) {
            $parts = explode('-', $id, 3);
            $agenda = Agenda::findOrFail($parts[1] ?? null);

            $terkunci = (filled($agenda->nomor_memo_pns) && in_array($agenda->status_pns, ['sakti', 'sp2d'], true))
                || (filled($agenda->nomor_memo_non_pns) && in_array($agenda->status_non_pns, ['sakti', 'sp2d'], true));

            if ($terkunci) {
                return redirect()->route('memo.index')
                    ->with('error', 'Agenda tidak bisa dihapus karena memo sudah SAKTI/SP2D. '
                        . 'Ubah status memo menjadi Batal terlebih dahulu jika memang dibatalkan.');
            }

            // Memo agenda yang sudah dikirim ke Keuangan tidak boleh hilang begitu saja.
            foreach (['pns' => $agenda->nomor_memo_pns, 'non_pns' => $agenda->nomor_memo_non_pns] as $jenis => $nomor) {
                if (filled($nomor) && !MemoTahapService::bisaDiedit("agenda-{$agenda->id}-{$jenis}")) {
                    return redirect()->route('memo.index')
                        ->with('error', 'Agenda tidak bisa dihapus karena memonya sudah dikirim ke Keuangan. Ajukan pengembalian terlebih dahulu.');
                }
            }

            // Agenda dan nomor memonya satu kesatuan: hapus agendanya,
            // memo PNS dan Non-PNS yang nempel ikut hilang, begitu juga jejak tahapnya.
            DB::transaction(function () use ($agenda) {
                $agenda->delete();
                MemoTahapService::hapusJejak(MemoTahapService::kunciAgenda($agenda->id));
            });

            return redirect()->route('memo.index')
                ->with('success', 'Agenda beserta nomor memonya berhasil dihapus.');
        }

        if (str_starts_with($id, 'entry-')) {
            $entryId = substr($id, strlen('entry-'));

            $entry = MemoEntry::findOrFail($entryId);

            if (!in_array($entry->status_anggaran ?? 'outstanding', ['outstanding', 'batal'], true)) {
                return redirect()->route('memo.index')
                    ->with('error', 'Memo tidak bisa dihapus karena sudah SAKTI/SP2D. '
                        . 'Ubah status memo menjadi Batal terlebih dahulu jika memang dibatalkan.');
            }

            if (!MemoTahapService::bisaDiedit($id)) {
                return redirect()->route('memo.index')
                    ->with('error', 'Memo sudah dikirim ke Keuangan, tidak bisa dihapus. Ajukan pengembalian terlebih dahulu.');
            }

            DB::transaction(function () use ($entry, $id) {
                $entry->delete();
                MemoTahapService::hapusJejak([$id]);
            });

            return redirect()->route('memo.index')
                ->with('success', 'Nomor memo berhasil dihapus.');
        }

        abort(404);
    }

    public function pdf(int $memo)
    {
        // Keuangan membaca lintas unit; unit kerja tetap dibatasi scope 'unit' (404 untuk unit lain).
        $memo = MemoEntry::query()
            ->when(auth()->user()?->can('kelola-anggaran'), fn($q) => $q->withoutGlobalScope('unit'))
            ->findOrFail($memo);

        $memo->load(['pic', 'ppk', 'bendahara', 'penanggungJawab', 'petugasVerifikasi']);

        $makOption = MakOption::where('mak', $memo->mak)->first();
        $kodeParts = explode('.', $memo->mak ?? '');
        $kodeGiat = count($kodeParts) === 6 ? implode('.', array_slice($kodeParts, 0, 3)) : null;
        $kodeKomponen = $kodeParts[3] ?? null;
        $kodeAkunAp = $kodeParts[4] ?? null;
        $kodeBelanja = $kodeParts[5] ?? null;

        // Tentukan "Hal" berdasarkan jenis memo
        $halMemo = $memo->hal_memo;

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
            'halMemo' => $halMemo,
            'lampiranMemo' => '1 (satu) Berkas', // tambahan
        ])->setPaper('a4', 'portrait');

        $namaFile = 'Memorandum-' . str_replace(['/', '\\'], '-', $memo->nomor_memo) . '.pdf';

        return $pdf->stream($namaFile);
    }

    /**
     * Memo mandiri boleh diedit hanya kalau status anggarannya masih outstanding
     * DAN tahap alurnya draft/dikembalikan (belum dikirim ke Keuangan).
     */
    private function bolehEdit(MemoEntry $memo): bool
    {
        return ($memo->status_anggaran ?? 'outstanding') === 'outstanding'
            && MemoTahapService::bisaDiedit("entry-{$memo->id}");
    }

    /**
     * Riwayat per memo dari empat sumber: pengiriman, verifikasi K2/K3, perubahan status
     * anggaran, dan pengajuan pengembalian. Tiap sumber dibaca dengan satu query untuk
     * semua memo. Kunci memo yang masuk sudah lolos filter unit di index(), jadi log
     * unit lain tidak ikut terbaca.
     *
     * @param  string[]  $memoKeys  mis. ['entry-1', 'agenda-2-pns']
     * @return array<string, array<int, array{waktu:string, judul:string, detail:?string, oleh:?string}>>
     */
    private function petaLog(array $memoKeys): array
    {
        if ($memoKeys === []) {
            return [];
        }

        $baris = []; // memo_key => daftar baris log
        $tambah = function (string $key, $waktu, string $judul, ?string $detail, $userId) use (&$baris) {
            if ($waktu) {
                $baris[$key][] = [
                    'waktu' => (string) $waktu,
                    'judul' => $judul,
                    'detail' => $detail,
                    'user_id' => $userId,
                ];
            }
        };

        // 1. Pengiriman ke Keuangan
        foreach (DB::table('memo_progres')->whereIn('memo_key', $memoKeys)->whereNotNull('dikirim_at')->get() as $r) {
            $tambah($r->memo_key, $r->dikirim_at, 'Dikirim ke Keuangan', null, $r->dikirim_oleh);
        }

        // 2. Verifikasi K2 / K3
        foreach (DB::table('memo_verifikasi_logs')->whereIn('memo_key', $memoKeys)->get() as $r) {
            $tambah(
                $r->memo_key,
                $r->created_at,
                "Verifikasi {$r->gerbang}: " . ($r->lengkap_sesuai ? 'lolos' : 'tidak lolos'),
                $r->alasan,
                $r->user_id
            );
        }

        // 3. Perubahan status anggaran. Tabel ini memakai (tipe, id, jenis), bukan memo_key.
        $entryIds = [];
        $agendaIds = [];
        foreach ($memoKeys as $key) {
            if (preg_match('/^entry-(\d+)$/', $key, $m)) {
                $entryIds[] = (int) $m[1];
            } elseif (preg_match('/^agenda-(\d+)-/', $key, $m)) {
                $agendaIds[] = (int) $m[1];
            }
        }

        $statusLogs = DB::table('memo_status_logs')
            ->where(function ($q) use ($entryIds, $agendaIds) {
                $q->where(fn($w) => $w->where('memo_type', 'entry')->whereIn('memo_id', $entryIds))
                    ->orWhere(fn($w) => $w->where('memo_type', 'agenda')->whereIn('memo_id', $agendaIds));
            })
            ->get();

        foreach ($statusLogs as $r) {
            $key = $r->memo_type === 'entry'
                ? "entry-{$r->memo_id}"
                : "agenda-{$r->memo_id}-{$r->jenis}";

            $tambah($key, $r->created_at, "Status anggaran: {$r->dari} → {$r->ke}", $r->alasan, $r->user_id);
        }

        // 4. Pengajuan pengembalian
        foreach (DB::table('memo_pengajuan_kembali')->whereIn('memo_key', $memoKeys)->get() as $r) {
            $tambah($r->memo_key, $r->created_at ?? null, 'Pengajuan pengembalian diajukan', $r->alasan, null);

            if ($r->status !== 'menunggu') {
                $tambah(
                    $r->memo_key,
                    $r->diputus_at ?? null,
                    'Pengajuan ' . $r->status,
                    $r->catatan_putusan ?? null,
                    $r->diputus_oleh ?? null
                );
            }
        }

        // Nama pelaku, satu query.
        $userIds = collect($baris)->flatten(1)->pluck('user_id')->filter()->unique()->all();
        $nama = $userIds === []
            ? collect()
            : DB::table('users')->whereIn('id', $userIds)->pluck('name', 'id');

        $hasil = [];
        foreach ($baris as $key => $rows) {
            usort($rows, fn($a, $b) => strcmp($b['waktu'], $a['waktu'])); // terbaru di atas

            $hasil[$key] = array_map(fn($r) => [
                'waktu' => $r['waktu'],
                'judul' => $r['judul'],
                'detail' => $r['detail'],
                'oleh' => $r['user_id'] ? ($nama[$r['user_id']] ?? null) : null,
            ], $rows);
        }

        return $hasil;
    }

    /**
     * Tolak nomor yang angka urutnya sudah dipakai di unit mana pun
     * (memo_entries maupun agendas). Pesan sengaja tidak menyebut unit
     * atau uraian pemakainya.
     */
    private function pastikanNomorBelumDipakai(string $nomorMemo, ?int $ignoreEntryId = null): void
    {
        if (NomorMemoService::sudahDipakai($nomorMemo, $ignoreEntryId)) {
            throw ValidationException::withMessages([
                'nomor_memo' => 'Nomor memo ini sudah dipakai. Klik "Ambil Nomor" untuk mendapat nomor terbaru.',
            ]);
        }
    }

    /**
     * Aturan validasi bersama untuk store(), update(), dan MemoTahapController::uji().
     * Daftar jenis memo diambil dari MemoEntry::JENIS, jadi nambah jenis baru
     * cukup di model.
     */
    public static function rules(): array
    {
        return [
            // Wajib diawali angka urut, karena nomor_urut diekstrak dari situ.
            // Tanpa ini nomor_urut jadi 0 dan lolos dari cek nomor kembar.
            'nomor_memo' => ['required', 'string', 'regex:/^\s*\d+/'],
            'jenis_memo' => ['required', Rule::in(array_keys(MemoEntry::JENIS))],
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
            'tipe_pembayaran' => ['required', Rule::in(array_keys(MemoEntry::TIPE_PEMBAYARAN))],
        ];
    }
}