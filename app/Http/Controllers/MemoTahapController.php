<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
use App\Services\MemoPengajuanService;
use App\Services\MemoTahapService;
use App\Services\NomorMemoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MemoTahapController extends Controller
{
    // ---- Unit kerja (dibatasi global scope 'unit' lewat findOrFail) ----

    public function kirim(string $memo)
    {
        $this->pastikanAkses($memo);

        // Validasi (langkah 2 / K1) dijalankan di dalam transaksi kirim, setelah baris dikunci.
        return $this->jalankan(
            fn() => MemoTahapService::kirim($memo, auth()->id(), fn() => $this->uji($memo)),
            'Memo berhasil dikirim ke Keuangan.'
        );
    }

    public function ajukan(Request $request, string $memo)
    {
        $this->pastikanAkses($memo);
        $data = $request->validate(['alasan' => 'required|string|max:1000']);

        return $this->jalankan(
            fn() => MemoPengajuanService::ajukan($memo, $data['alasan'], auth()->id()),
            'Pengajuan pengembalian dikirim, menunggu persetujuan Keuangan.'
        );
    }

    // ---- Keuangan (can:kelola-anggaran di route) ----

    public function putusan(Request $request, int $pengajuan)
    {
        $data = $request->validate([
            'setuju' => 'required|boolean',
            'catatan' => 'nullable|string|max:1000',
        ]);

        return $this->jalankan(
            fn() => MemoPengajuanService::putuskan($pengajuan, (bool) $data['setuju'], $data['catatan'] ?? null, auth()->id()),
            $data['setuju'] ? 'Pengajuan disetujui, memo dibuka kembali.' : 'Pengajuan ditolak.'
        );
    }

    public function verifikasi(Request $request, string $memo)
    {
        $data = $request->validate([
            'gerbang' => 'required|in:K2,K3',
            'lolos' => 'required|boolean',
            'alasan' => 'nullable|string|max:1000',
        ]);

        return $this->jalankan(
            fn() => MemoTahapService::verifikasi($memo, $data['gerbang'], (bool) $data['lolos'], $data['alasan'] ?? null, auth()->id()),
            $data['lolos'] ? 'Verifikasi lolos.' : 'Memo dikembalikan ke unit kerja.'
        );
    }

    public function inputSakti(Request $request, string $memo)
    {
        $data = $request->validate(['tanggal' => 'nullable|date']);

        return $this->jalankan(
            fn() => MemoTahapService::inputSakti($memo, $data['tanggal'] ?? null, auth()->id()),
            'Memo tercatat di SAKTI.'
        );
    }

    public function selesaiOtp(string $memo)
    {
        return $this->jalankan(
            fn() => MemoTahapService::selesaiOtp($memo),
            'OTP PPK selesai, pengembalian ditutup.'
        );
    }

    // ---- Helper ----

    private function jalankan(\Closure $aksi, string $pesanSukses)
    {
        try {
            $aksi();
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $pesanSukses);
    }

    // findOrFail memakai global scope 'unit', jadi unit lain mendapat 404.
    private function pastikanAkses(string $memo): void
    {
        if (preg_match('/^entry-(\d+)$/', $memo, $m)) {
            MemoEntry::findOrFail($m[1]);
            return;
        }

        if (preg_match('/^agenda-(\d+)-(pns|non_pns)$/', $memo, $m)) {
            $agenda = Agenda::findOrFail($m[1]);
            $nomor = $m[2] === 'pns' ? $agenda->nomor_memo_pns : $agenda->nomor_memo_non_pns;
            abort_if(blank($nomor), 404);
            return;
        }

        abort(404);
    }

    // Langkah 2 + K1: jalankan ulang aturan yang sama dengan store/update.
    // Melempar ValidationException kalau ada yang perlu diperbaiki.
    private function uji(string $memo): void
    {
        if (!preg_match('/^entry-(\d+)$/', $memo, $m)) {
            // TODO memo agenda: validasinya ada di AgendaController, belum saya lihat.
            return;
        }

        $entry = MemoEntry::findOrFail($m[1]);
        $rules = MemoController::rules();

        Validator::validate($entry->only(array_keys($rules)), $rules);

        if (NomorMemoService::sudahDipakai($entry->nomor_memo, $entry->id)) {
            throw ValidationException::withMessages([
                'nomor_memo' => 'Nomor memo ini bentrok dengan memo lain. Perbaiki nomor sebelum mengirim.',
            ]);
        }
    }
}