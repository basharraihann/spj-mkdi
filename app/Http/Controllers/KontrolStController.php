<?php

namespace App\Http\Controllers;

use App\Models\SuratTugas;
use App\Models\SuratTugasKontrol;
use App\Models\Unit;
use App\Models\User;
use App\Services\SuratTugasNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Lembar kontrol Surat Tugas (alur Sesdep).
 *
 * Alur: Konseptor (TU) -> Eselon 2 unit pembuat -> Kontrol & penomoran (admin Sesdep)
 *       -> Persetujuan Sesdep -> TTD diproses admin TU Sesdep (lihat SuratTugasController@terbitkan).
 *
 * Petugas diturunkan otomatis dari unit pembuat. Unit tanpa Sesdep (mis. Deputi) belum memakai alur ini.
 */
class KontrolStController extends Controller
{
    public function __construct(private SuratTugasNumberService $numbers)
    {
    }

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

        // Unit pembuat: dari ST, lalu dari akun, lalu (khusus admin tanpa unit) dari input.
        $request->validate(['unit_id' => ['nullable', 'integer', 'exists:units,id']]);
        $unit = Unit::find($suratTugas->unit_id ?? $user->unit_id ?? $request->input('unit_id'));

        if (!$unit) {
            return $this->kembali($suratTugas, 'Unit pembuat ST belum diketahui. Pilih unit terlebih dahulu.', 'error');
        }

        // Kode unit berakhiran " D<n>" (Asdep 3 D2, Sekretariat D2) -> Sekretariat D<n>
        if (!preg_match('/\sD(\d)$/', (string) $unit->kode, $m)) {
            return $this->kembali($suratTugas, "Unit {$unit->nama} belum memakai alur persetujuan Sesdep.", 'error');
        }

        $sekretariat = Unit::where('kode', 'Sekretariat D' . $m[1])->first();

        // orderBy('id') supaya hasil deterministik kalau satu unit punya lebih dari satu user di role yang sama.
        $es2 = User::where('unit_id', $unit->id)->where('role', User::ROLE_ESELON2)->orderBy('id')->first();
        $sesdep = $sekretariat
            ? User::where('unit_id', $sekretariat->id)->where('role', User::ROLE_ESELON2)->orderBy('id')->first()
            : null;
        $adminSesdep = $sekretariat
            ? User::where('unit_id', $sekretariat->id)->where('role', User::ROLE_TU)->orderBy('id')->first()
            : null;

        if (!$es2 || !$sesdep || !$adminSesdep) {
            return $this->kembali(
                $suratTugas,
                'Petugas belum lengkap. Pastikan unit pembuat punya Eselon 2, dan Sekretariat-nya punya Eselon 2 (Sesdep) dan TU (admin Sesdep).',
                'error'
            );
        }

        // Pembuat dari unit Sesdep sendiri: Eselon 2 = Sesdep, jadi tahap Eselon 2 dilewati.
        $lewatEs2 = (int) $es2->id === (int) $sesdep->id;

        $petugas = [
            1 => $user->id,
            2 => $es2->id,
            3 => $adminSesdep->id,
            4 => $sesdep->id,
        ];

        DB::transaction(function () use ($suratTugas, $unit, $petugas, $lewatEs2) {
            if (!$suratTugas->unit_id) {
                $suratTugas->update(['unit_id' => $unit->id]);
            }

            foreach (SuratTugasKontrol::TAHAP as $urutan => $tahap) {
                if ($urutan === 2 && $lewatEs2) {
                    continue;
                }

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

        return $this->kembali($suratTugas, 'Lembar kontrol dibuat dan diajukan. ST kini dikunci selama proses persetujuan.');
    }

    public function aksi(Request $request, SuratTugasKontrol $kontrol): RedirectResponse
    {
        $request->validate([
            'aksi' => ['required', 'in:setujui,tolak,ulang'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        $user = Auth::user();
        $st = $kontrol->suratTugas;

        if ($st->status !== 'draf') {
            return $this->kembali($st, 'ST sudah diterbitkan; lembar kontrol tidak dapat diubah.', 'error');
        }

        // Ajukan ulang setelah revisi: kembali ke tahap pertama setelah konseptor.
        // Nomor yang sudah terambil tetap dipertahankan (ambilNomor tidak mengambil ulang).
        if ($request->aksi === 'ulang') {
            abort_unless($user->isAdmin() || (int) $st->created_by === (int) $user->id, 403);

            if (!$st->kontrol()->where('status', 'ditolak')->exists()) {
                return $this->kembali($st, 'Tidak ada tahap yang ditolak, jadi tidak perlu diajukan ulang.', 'error');
            }

            $st->kontrol()->where('urutan', '>', 1)->update([
                'status' => 'menunggu',
                'catatan' => null,
                'acted_at' => null,
            ]);

            return $this->kembali($st, 'Revisi diajukan ulang dari tahap pertama.');
        }

        abort_unless($user->isAdmin() || (int) $kontrol->petugas_id === (int) $user->id, 403);

        $setujui = $request->aksi === 'setujui';

        // Menolak wajib disertai catatan agar konseptor tahu apa yang direvisi.
        if (!$setujui && !filled($request->input('catatan'))) {
            return $this->kembali($st, 'Catatan revisi wajib diisi saat menolak.', 'error');
        }

        $nomor = null;

        try {
            DB::transaction(function () use ($kontrol, $request, $setujui, $st, &$nomor) {
                // Kunci baris tahap supaya dua klik bersamaan tidak lolos dua kali.
                $k = SuratTugasKontrol::lockForUpdate()->findOrFail($kontrol->id);

                if ($k->status !== 'menunggu') {
                    throw new \DomainException('Tahap ini sudah diproses.');
                }

                $belumSelesai = $st->kontrol()
                    ->where('urutan', '<', $k->urutan)
                    ->where('status', '!=', 'disetujui')
                    ->exists();
                if ($belumSelesai) {
                    throw new \DomainException('Tahap sebelumnya belum disetujui.');
                }

                $k->update([
                    'status' => $setujui ? 'disetujui' : 'ditolak',
                    'catatan' => $request->input('catatan'),
                    'acted_at' => now(),
                ]);

                // Nomor diambil saat admin Sesdep menyetujui. Sudah bernomor = tidak diambil lagi.
                if ($setujui && $k->tahap === SuratTugasKontrol::TAHAP_NOMOR) {
                    $nomor = $this->numbers
                        ->ambilNomor($st->id, $st->tanggal->format('Y-m-d'), $st->kode_klasifikasi)
                        ->nomor;
                }
            });
        } catch (\DomainException $e) {
            return $this->kembali($st, $e->getMessage(), 'error');
        }

        if (!$setujui) {
            return $this->kembali($st, 'Tahap ditolak. TU pembuat dapat merevisi lalu mengajukan ulang.');
        }

        return $this->kembali($st, $nomor ? "Tahap disetujui. Nomor surat: {$nomor}." : 'Tahap disetujui.');
    }

    public function cetak(SuratTugas $suratTugas)
    {
        abort_unless(
            SuratTugas::dapatDilihat(Auth::user())->whereKey($suratTugas->id)->exists(),
            403
        );

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