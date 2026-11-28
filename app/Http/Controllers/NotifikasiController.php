<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotifikasiController extends Controller
{
    /** Data untuk lonceng: jumlah belum dibaca + 10 notifikasi terbaru. */
    public function data(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'belum' => $user->unreadNotifications()->count(),
            'daftar' => $user->notifications()->limit(10)->get()->map(fn($n) => [
                'id' => $n->id,
                'judul' => $n->data['judul'] ?? 'Notifikasi',
                'pesan' => $n->data['pesan'] ?? '',
                'waktu' => $n->created_at->diffForHumans(),
                'dibaca' => $n->read_at !== null,
                'url' => route('notifikasi.buka', $n->id),
            ])->values(),
        ]);
    }

    /** Tandai satu notifikasi dibaca, lalu arahkan ke halaman tujuannya. */
    public function buka(Request $request, string $id): RedirectResponse
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();

        return redirect($n->data['url'] ?? route('disposisi.index'));
    }

    public function bacaSemua(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['ok' => true]);
    }
}
