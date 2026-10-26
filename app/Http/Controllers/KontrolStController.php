<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Models\SuratTugasKontrol;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Lembar kontrol Surat Tugas (opsional per ST).
 * Jika sebuah ST memiliki lembar kontrol, ST baru bisa diterbitkan setelah semua tahap disetujui.
 */
class KontrolStController extends Controller
{
    public function mulai(Request $request, SuratTugas $suratTugas): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || (int) $suratTugas->created_by === (int) $user->id, 403);

        if ($suratTugas->status !== 'draf') {
            return $this->kembali($suratTugas, 'Lembar kontrol hanya bisa dibuat untuk ST berstatus draf.', 'error');
        }
        if ($suratTugas->kontrol()->exists()) {
            return $this->kembali($suratTugas, 'Lembar kontrol sudah ada untuk ST ini.', 'error');
        }

        $v = $request->validate([
            'pemeriksa_id' => ['required', 'integer', 'exists:users,id'],
            'es2_id' => ['required', 'integer', 'exists:users,id'],
            'admin_sesdep_id' => ['required', 'integer', 'exists:users,id'],
            'sesdep_id' => ['required', 'integer', 'exists:users,id'],
        ], [
            '*.required' => 'Semua petugas lembar kontrol wajib dipilih.',
        ]);

        $petugas = [
            1 => $user->id,
            2 => $v['pemeriksa_id'],
            3 => $v['es2_id'],
            4 => $v['admin_sesdep_id'],
            5 => $v['sesdep_id'],
        ];

        DB::transaction(function () use ($suratTugas, $petugas) {
            foreach (SuratTugasKontrol::TAHAP as $urutan => $tahap) {
                SuratTugasKontrol::create([
                    'surat_tugas_id' => $suratTugas->id,
                    'urutan' => $urutan,
                    'tahap' => $tahap,
                    'petugas_id' => $petugas[$urutan],
                    // Konseptor (pembuat) otomatis tercatat telah menyusun konsep
                    'status' => $urutan === 1 ? 'disetujui' : 'menunggu',
                    'acted_at' => $urutan === 1 ? now() : null,
                ]);
            }
        });

        return $this->kembali($suratTugas, 'Lembar kontrol dibuat. Tahap pemeriksaan menunggu petugas terkait.');
    }

    public function aksi(Request $request, SuratTugasKontrol $kontrol): RedirectResponse
    {
        $request->validate([
            'aksi' => ['required', 'in:setujui,tolak,ulang'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $st = $kontrol->suratTugas;

        if ($request->aksi === 'ulang') {
            abort_unless($user->isAdmin() || (int) $st->created_by === (int) $user->id, 403);

            $st->kontrol()->where('urutan', '>', 1)->update([
                'status' => 'menunggu',
                'catatan' => null,
                'acted_at' => null,
            ]);

            return $this->kembali($st, 'Lembar kontrol diulang dari tahap pemeriksaan.');
        }

        abort_unless($user->isAdmin() || (int) $kontrol->petugas_id === (int) $user->id, 403);

        if ($st->status !== 'draf') {
            return $this->kembali($st, 'ST sudah diterbitkan; lembar kontrol tidak dapat diubah.', 'error');
        }
        if ($kontrol->status !== 'menunggu') {
            return $this->kembali($st, 'Tahap ini sudah diproses.', 'error');
        }

        $belumSelesai = $st->kontrol()
            ->where('urutan', '<', $kontrol->urutan)
            ->where('status', '!=', 'disetujui')
            ->exists();
        if ($belumSelesai) {
            return $this->kembali($st, 'Tahap sebelumnya belum disetujui.', 'error');
        }

        $kontrol->update([
            'status' => $request->aksi === 'setujui' ? 'disetujui' : 'ditolak',
            'catatan' => $request->input('catatan'),
            'acted_at' => now(),
        ]);

        return $this->kembali($st, $request->aksi === 'setujui' ? 'Tahap disetujui.' : 'Tahap ditolak; konseptor dapat mengulang lembar kontrol.');
    }

    public function cetak(SuratTugas $suratTugas)
    {
        $suratTugas->load('kontrol.petugas.pegawai');

        return Pdf::loadView('pdf.lembar-kontrol-st', ['st' => $suratTugas])
            ->setPaper('a4', 'portrait')
            ->stream('Lembar Kontrol ST ' . str_replace('/', '-', $suratTugas->nomor ?? 'Draf ' . $suratTugas->id) . '.pdf');
    }

    private function kembali(SuratTugas $st, string $pesan, string $tipe = 'success'): RedirectResponse
    {
        return redirect()->route('surat-tugas.show', $st)->with($tipe, $pesan);
    }
}
