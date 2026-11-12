<?php

namespace App\Http\Controllers;

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