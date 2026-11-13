<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
use App\Services\MemoStatusService;
use App\Services\MemoTahapService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Ubah status anggaran satu memo (dari dropdown di daftar memo).
 * Status SAKTI tidak bisa dipilih dari sini: hanya lewat alur tahap (Input SAKTI).
 */
class MemoStatusController extends Controller
{
    public function update(Request $request, string $memo): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(MemoStatusService::STATUS)],
            'tanggal' => ['nullable', 'date'],
            'alasan' => ['nullable', 'string', 'max:255'],
        ]);

        // MemoStatusService::ubah() melepas scope 'unit' (alur Keuangan lintas unit).
        // Supaya unit kerja tidak bisa mengubah memo unit lain, cek kepemilikan di sini:
        // findOrFail di bawah kena scope 'unit', jadi unit lain mendapat 404.
        if (!$request->user()?->can('kelola-anggaran')) {
            try {
                [$tipe, $id] = MemoStatusService::parse($memo);
            } catch (\DomainException $e) {
                abort(404);
            }

            ($tipe === 'entry' ? MemoEntry::class : Agenda::class)::findOrFail($id);
        }

        try {
            MemoTahapService::ubahStatusManual(
                $memo,
                $data['status'],
                $data['tanggal'] ?? null,
                $data['alasan'] ?? null,
                $request->user()?->id
            );
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Status memo diperbarui.');
    }
}