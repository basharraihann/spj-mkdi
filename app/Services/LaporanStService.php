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

    /**
     * Buat tugas "Isi Laporan" untuk setiap peserta ST yang punya akun.
     * Aman dipanggil ulang (tidak membuat duplikat). Mengembalikan jumlah akun yang ditugasi.
     */
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

    /** Admin, atau akun yang masih punya tugas lapor berstatus menunggu untuk ST ini. */
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

    /** Boleh membuka/mengunduh berkas laporan: admin, pembuat ST, atau peserta yang ditugasi lapor. */
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

    /**
     * Simpan draf, atau kirim. Saat kirim: laporan dikunci, tugas pengirim selesai,
     * tugas peserta lain ditutup, dan ST otomatis berstatus selesai.
     *
     * Hanya kolom yang ADA di $data yang diubah. Jadi form yang tidak lagi mengirim
     * notulensi / materi / keputusan / tindak_lanjut tidak akan menghapus isi laporan yang sudah tersimpan.
     *
     * @param  array{notulensi?:?string, materi?:?string, keputusan?:?string, tindak_lanjut?:?array}  $data
     * @throws \DomainException
     */
    public function simpan(SuratTugas $st, User $user, array $data, bool $kirim, ?UploadedFile $berkas = null): LaporanSt
    {
        $pathBaru = null;
        $pathLama = null;

        try {
            $laporan = DB::transaction(function () use ($st, $user, $data, $kirim, $berkas, &$pathBaru, &$pathLama) {
                // kunci baris ST supaya dua peserta yang mengirim bersamaan tidak saling menimpa
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

                if ($berkas) {
                    $pathLama = $laporan->file_laporan_path;
                    $pathBaru = $berkas->store('laporan-st/' . $st->id, self::DISK);
                    $atribut['file_laporan_path'] = $pathBaru;
                    $atribut['file_laporan_nama'] = mb_substr($berkas->getClientOriginalName(), 0, 180);
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
            // transaksi gagal: buang berkas yang sempat tersimpan supaya tidak jadi sampah
            if ($pathBaru) {
                Storage::disk(self::DISK)->delete($pathBaru);
            }
            throw $e;
        }

        // sukses: berkas lama yang digantikan boleh dihapus
        if ($pathBaru && $pathLama && $pathLama !== $pathBaru) {
            Storage::disk(self::DISK)->delete($pathLama);
        }

        return $laporan;
    }

    /** Ganti seluruh tindak lanjut dengan isian terbaru; baris tanpa uraian dibuang. */
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