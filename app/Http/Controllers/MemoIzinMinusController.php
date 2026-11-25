<?php

namespace App\Http\Controllers;

use App\Services\MemoIzinMinusService;
use Illuminate\Http\Request;

/**
 * Persetujuan izin minus (nominal memo melebihi sisa efektif MAK).
 * Hanya PPK / Keuangan (Gate setujui-minus, diatur di AppServiceProvider).
 */
class MemoIzinMinusController extends Controller
{
    public function putusan(Request $request, int $izin)
    {
        $data = $request->validate([
            'setuju' => 'required|boolean',
            'catatan' => 'nullable|string|max:1000',
        ]);

        try {
            MemoIzinMinusService::putuskan($izin, (bool) $data['setuju'], $data['catatan'] ?? null, auth()->id());
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', $data['setuju']
            ? 'Izin minus disetujui. Memo sudah bisa dikirim ke Keuangan.'
            : 'Izin minus ditolak. Unit kerja perlu mengubah nominal atau MAK.');
    }
}
