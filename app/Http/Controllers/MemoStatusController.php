<?php

namespace App\Http\Controllers;

use App\Services\MemoStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Ubah status anggaran satu memo (dari dropdown di daftar memo).
 * Logikanya ada di MemoStatusService, yang juga dipakai aksi massal di halaman monitoring.
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
            MemoStatusService::ubah(
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