<?php

namespace App\Services;

use App\Models\LaporanSt;
use App\Models\LaporanStTindakLanjut;
use App\Models\LaporanStTugas;
use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LaporanStService
{
    /** Disk privat untuk berkas laporan (tidak bisa diakses lewat URL langsung). */
    public const DISK = 'local';

    public function buatTugas(SuratTugas $st): int
    {
        $pegawaiIds = $st->peserta()->whereNotNull('pegawai_id')->pluck('pegawai_id');
        $userIds = User::whereIn('pegawai_id', $pegawaiIds)->pluck('id');

        foreach ($userIds as $userId) {
            LaporanStTugas::firstOrCreate(
                ['surat_tugas_id' => $st->id, 'user_id' => $userId],
                ['status' => LaporanStTugas::STATUS_MENUNGGU]
            );
        }

        return $userIds->count();
    }

    public function bolehIsi(SuratTugas $st, ?User $user): bool
    {
        if (!$user || $st->status !== 'terbit') {
            return false;
        }

        if (LaporanSt::where('surat_tugas_id', $st->id)->where('status', 'dikirim')->exists()) {
            return false;
        }

        return $user->isAdmin()
            || LaporanStTugas::where('surat_tugas_id', $st->id)
                ->where('user_id', $user->id)
                ->where('status', LaporanStTugas::STATUS_MENUNGGU)
                ->exists();
    }

    public function bolehUnduh(SuratTugas $st, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || (int) $st->created_by === (int) $user->id) {
            return true;
        }

        return LaporanStTugas::where('surat_tugas_id', $st->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function simpan(
        SuratTugas $st,
        User $user,
        array $data,
        bool $kirim,
        ?UploadedFile $berkas = null,
        ?UploadedFile $fileMateri = null
    ): LaporanSt {
        $pathBaru = null;
        $pathLama = null;

        try {
            $laporan = DB::transaction(function () use ($st, $user, $data, $kirim, $berkas, $fileMateri, &$pathBaru, &$pathLama) {
                $st = SuratTugas::lockForUpdate()->findOrFail($st->id);

                if ($st->status !== 'terbit') {
                    throw new \DomainException('Laporan hanya bisa diisi untuk ST yang berstatus Terbit.');
                }

                $laporan = LaporanSt::firstOrNew(['surat_tugas_id' => $st->id]);

                if ($laporan->exists && $laporan->isDikirim()) {
                    throw new \DomainException('Laporan sudah dikirim oleh peserta lain dan terkunci.');
                }

                $atribut = [
                    'status' => 'draf',
                    'diperbarui_oleh' => $user->id,
                ];

                foreach (['notulensi', 'materi', 'keputusan'] as $kolom) {
                    if (array_key_exists($kolom, $data)) {
                        $atribut[$kolom] = $data[$kolom];
                    }
                }

                // 1. Simpan Berkas Notulensi Utama
                if ($berkas) {
                    $pathLama = $laporan->file_laporan_path;
                    $pathBaru = $berkas->store('laporan-st/' . $st->id, self::DISK);
                    $atribut['file_laporan_path'] = $pathBaru;
                    $atribut['file_laporan_nama'] = mb_substr($berkas->getClientOriginalName(), 0, 180);
                }

                // 2. Simpan Berkas Materi ke storage & catat nama filenya di kolom materi
                if ($fileMateri) {
                    $fileMateri->storeAs('laporan-st/' . $st->id . '/materi', $fileMateri->getClientOriginalName(), self::DISK);
                    $atribut['materi'] = $fileMateri->getClientOriginalName();
                }

                $laporan->forceFill($atribut)->save();

                if (array_key_exists('tindak_lanjut', $data)) {
                    $this->syncTindakLanjut($laporan, $data['tindak_lanjut'] ?? []);
                }

                if ($kirim) {
                    $laporan->update([
                        'status' => 'dikirim',
                        'dikirim_oleh' => $user->id,
                        'dikirim_at' => now(),
                    ]);

                    LaporanStTugas::where('surat_tugas_id', $st->id)
                        ->where('user_id', $user->id)
                        ->update(['status' => LaporanStTugas::STATUS_SELESAI]);

                    LaporanStTugas::where('surat_tugas_id', $st->id)
                        ->where('status', LaporanStTugas::STATUS_MENUNGGU)
                        ->update(['status' => LaporanStTugas::STATUS_DITUTUP]);

                    $st->update(['status' => 'selesai']);
                }

                return $laporan;
            });
        } catch (\Throwable $e) {
            if ($pathBaru) {
                Storage::disk(self::DISK)->delete($pathBaru);
            }
            throw $e;
        }

        if ($pathBaru && $pathLama && $pathLama !== $pathBaru) {
            Storage::disk(self::DISK)->delete($pathLama);
        }

        return $laporan;
    }

    private function syncTindakLanjut(LaporanSt $laporan, array $rows): void
    {
        $laporan->tindakLanjut()->delete();

        $n = 0;
        foreach ($rows as $row) {
            $uraian = trim((string) ($row['uraian'] ?? ''));
            if ($uraian === '') {
                continue;
            }

            LaporanStTindakLanjut::create([
                'laporan_st_id' => $laporan->id,
                'urutan' => ++$n,
                'uraian' => $uraian,
                'pic' => trim((string) ($row['pic'] ?? '')) ?: null,
                'tenggat' => ($row['tenggat'] ?? null) ?: null,
            ]);
        }
    }
}