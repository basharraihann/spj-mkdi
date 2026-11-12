<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MemoTahapService
{
    public const TAHAP = ['draft', 'dikirim', 'lolos_spk', 'sakti', 'otp_ppk', 'ppspm', 'dikembalikan'];

    // Pengembalian tertutup hanya setelah OTP selesai (ppspm).
    private const BOLEH_KEMBALI = ['dikirim', 'lolos_spk', 'sakti', 'otp_ppk'];

    // Tahap yang sudah melewati langkah 5 (SAKTI), jadi harus melepas SAKTI saat dikembalikan.
    private const SUDAH_SAKTI = ['sakti', 'otp_ppk'];

    public static function bisaDiedit(string $memo): bool
    {
        return in_array(self::tahapSaatIni($memo), ['draft', 'dikembalikan'], true);
    }

    public static function tahapSaatIni(string $memo): string
    {
        $row = DB::table('memo_progres')->where('memo_key', $memo)->first();
        return $row->tahap ?? self::tahapAwal($memo);
    }

    // Untuk service pengajuan: ambil tahap dengan lock (harus dipanggil di dalam transaksi).
    public static function tahapTerkunci(string $memo): string
    {
        return self::kunci($memo)->tahap;
    }

    public static function bolehDikembalikan(string $tahap): bool
    {
        return in_array($tahap, self::BOLEH_KEMBALI, true);
    }

    // Dipakai daftar memo: satu panggilan untuk semua memo, tanpa N+1.
    // $statusPerKey = ['entry-12' => 'outstanding', 'agenda-3-pns' => 'sakti', ...]
    public static function petaTahap(array $statusPerKey): array
    {
        if ($statusPerKey === []) {
            return [];
        }

        $ada = DB::table('memo_progres')
            ->whereIn('memo_key', array_keys($statusPerKey))
            ->pluck('tahap', 'memo_key')->all();

        $hasil = [];
        foreach ($statusPerKey as $key => $status) {
            $hasil[$key] = $ada[$key] ?? self::dariStatus($status);
        }
        return $hasil;
    }

    public static function dariStatus(?string $status): string
    {
        return match ($status) {
            'sakti' => 'sakti',
            'sp2d' => 'ppspm',
            default => 'draft', // outstanding, batal, atau kosong
        };
    }

    // Langkah 3. $validasi (langkah 2 / K1) dijalankan di dalam transaksi, setelah baris
    // progres terkunci, jadi nomor tidak bisa direbut di antara validasi dan pengiriman.
    // Boleh melempar ValidationException; transaksi otomatis dibatalkan.
    public static function kirim(string $memo, int $userId, ?\Closure $validasi = null): void
    {
        DB::transaction(function () use ($memo, $userId, $validasi) {
            self::kunci($memo);

            if ((self::statusAnggaran($memo) ?? 'outstanding') !== 'outstanding') {
                throw new \DomainException('Hanya memo berstatus outstanding yang bisa dikirim.');
            }

            $validasi?->__invoke();

            self::pindah($memo, ['draft', 'dikembalikan'], 'dikirim', function () use ($memo, $userId) {
                DB::table('memo_progres')->where('memo_key', $memo)
                    ->update(['dikirim_at' => now(), 'dikirim_oleh' => $userId]);
            });
        });
    }

    // K2 (gerbang='K2') hanya dari tahap dikirim; K3 (gerbang='K3') hanya dari tahap sakti.
    public static function verifikasi(string $memo, string $gerbang, bool $lolos, ?string $alasan, int $userId): void
    {
        if (!in_array($gerbang, ['K2', 'K3'], true)) {
            throw new \DomainException('Gerbang tidak dikenal.');
        }
        if (!$lolos && blank($alasan)) {
            throw new \DomainException('Alasan wajib diisi jika tidak lengkap atau tidak sesuai.');
        }

        DB::transaction(function () use ($memo, $gerbang, $lolos, $alasan, $userId) {
            $syarat = $gerbang === 'K2' ? 'dikirim' : 'sakti';

            // Cek tahap dulu (dengan lock), sebelum log ditulis, untuk lolos maupun tolak.
            $sekarang = self::kunci($memo)->tahap;
            if ($sekarang !== $syarat) {
                throw new \DomainException("Verifikasi {$gerbang} tidak berlaku untuk tahap {$sekarang}.");
            }

            DB::table('memo_verifikasi_logs')->insert([
                'memo_key' => $memo,
                'gerbang' => $gerbang,
                'lengkap_sesuai' => $lolos,
                'alasan' => $alasan,
                'user_id' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($lolos) {
                self::pindah($memo, [$syarat], $gerbang === 'K2' ? 'lolos_spk' : 'otp_ppk');
            } else {
                self::kembalikan($memo, "Ditolak di {$gerbang}: {$alasan}", $userId);
            }
        });
    }

    // Langkah 5: Keuangan Sesdep input ke SAKTI.
    public static function inputSakti(string $memo, ?string $tanggal, int $userId): void
    {
        DB::transaction(function () use ($memo, $tanggal, $userId) {
            self::pindah($memo, ['lolos_spk'], 'sakti');

            if (MemoStatusService::ubah($memo, 'sakti', $tanggal, null, $userId) === false) {
                throw new \DomainException('Status anggaran memo tidak berubah.');
            }
        });
    }

    // Langkah 7 selesai: pengembalian ditutup, pengajuan yang masih menunggu otomatis ditolak.
    public static function selesaiOtp(string $memo): void
    {
        DB::transaction(function () use ($memo) {
            self::pindah($memo, ['otp_ppk'], 'ppspm');

            self::gugurkanPengajuan($memo, 'OTP sudah selesai, pengembalian ditutup.');
        });
    }

    // Dipakai saat K2/K3 tidak lolos, dan saat pengajuan unit kerja disetujui.
    public static function kembalikan(string $memo, string $alasan, int $userId): void
    {
        DB::transaction(function () use ($memo, $alasan, $userId) {
            $dari = self::kunci($memo)->tahap;

            if (!in_array($dari, self::BOLEH_KEMBALI, true)) {
                throw new \DomainException("Memo di tahap {$dari} tidak bisa dikembalikan.");
            }

            // Yang sudah melewati langkah 5 harus melepas SAKTI dan sisa MAK.
            if (in_array($dari, self::SUDAH_SAKTI, true)) {
                if (MemoStatusService::ubah($memo, 'outstanding', null, $alasan, $userId) === false) {
                    throw new \DomainException('Status anggaran memo tidak berubah, pengembalian dibatalkan.');
                }
            }

            DB::table('memo_progres')->where('memo_key', $memo)
                ->update(['tahap' => 'dikembalikan', 'updated_at' => now()]);

            // Kalau memo kembali lewat K2/K3, pengajuan yang masih menunggu otomatis gugur.
            // (Pengajuan yang sedang disetujui sudah ditulis 'disetujui' lebih dulu oleh putuskan().)
            self::gugurkanPengajuan($memo, 'Memo sudah dikembalikan.');
        });
    }

    // Satu-satunya jalan untuk ubah status anggaran manual (dropdown & aksi massal).
    // SAKTI dan pelepasan SAKTI hanya lewat inputSakti() / kembalikan().
    // Mengembalikan hasil MemoStatusService::ubah().
    public static function ubahStatusManual(string $memo, string $status, ?string $tanggal, ?string $alasan, ?int $userId)
    {
        return DB::transaction(function () use ($memo, $status, $tanggal, $alasan, $userId) {
            $tahap = self::kunci($memo)->tahap;

            // BARU: baris ini ditambahkan di atas $boleh
            $sekarang = self::statusAnggaran($memo) ?: 'outstanding';

            // GANTI: match yang lama diganti yang ini
            $boleh = match ($status) {
                'sp2d' => $tahap === 'ppspm',
                'sakti' => $tahap === 'ppspm' && $sekarang === 'sp2d', // koreksi SP2D yang salah input
                'outstanding' => in_array($tahap, ['draft', 'dikembalikan'], true),
                'batal' => in_array($tahap, ['draft', 'dikembalikan', 'ppspm'], true),
                default => false,
            };

            if (!$boleh) {
                throw new \DomainException(
                    $status === 'sakti'
                    ? 'Status SAKTI hanya bisa lewat aksi Input SAKTI setelah verifikasi K2 lolos.'
                    : "Status {$status} tidak bisa diubah manual saat memo di tahap {$tahap}."
                );
            }

            return MemoStatusService::ubah($memo, $status, $tanggal, $alasan, $userId);
        });
    }

    // Dipanggil saat memo/agenda dihapus, supaya memo baru dengan key yang sama
    // (auto-increment dipakai ulang) tidak mewarisi tahap lama.
    public static function hapusJejak(array $memoKeys): void
    {
        if ($memoKeys === []) {
            return;
        }

        DB::transaction(function () use ($memoKeys) {
            DB::table('memo_progres')->whereIn('memo_key', $memoKeys)->delete();
            DB::table('memo_verifikasi_logs')->whereIn('memo_key', $memoKeys)->delete();
            DB::table('memo_pengajuan_kembali')->whereIn('memo_key', $memoKeys)->delete();

            foreach ($memoKeys as $key) {
                [$tipe, $id, $jenis] = MemoStatusService::parse($key);

                DB::table('memo_status_logs')
                    ->where('memo_type', $tipe)->where('memo_id', $id)
                    ->when($jenis, fn($q) => $q->where('jenis', $jenis))
                    ->delete();
            }
        });
    }

    public static function kunciAgenda(int $agendaId): array
    {
        return ["agenda-{$agendaId}-pns", "agenda-{$agendaId}-non_pns"];
    }

    private static function gugurkanPengajuan(string $memo, string $catatan): void
    {
        DB::table('memo_pengajuan_kembali')
            ->where('memo_key', $memo)->where('status', 'menunggu')
            ->update([
                'status' => 'ditolak',
                'diputus_oleh' => null,
                'catatan_putusan' => $catatan,
                'diputus_at' => now(),
                'updated_at' => now(),
            ]);
    }

    private static function pindah(string $memo, array $dariBoleh, string $ke, ?\Closure $tambahan = null): void
    {
        DB::transaction(function () use ($memo, $dariBoleh, $ke, $tambahan) {
            $dari = self::kunci($memo)->tahap;

            if (!in_array($dari, $dariBoleh, true)) {
                throw new \DomainException("Perpindahan {$dari} → {$ke} tidak diizinkan.");
            }

            DB::table('memo_progres')->where('memo_key', $memo)
                ->update(['tahap' => $ke, 'updated_at' => now()]);

            $tambahan?->__invoke();
        });
    }

    // Ambil baris progres dengan lock; buat jika belum ada.
    private static function kunci(string $memo): object
    {
        DB::table('memo_progres')->insertOrIgnore([
            'memo_key' => $memo,
            'tahap' => self::tahapAwal($memo),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('memo_progres')->where('memo_key', $memo)->lockForUpdate()->first();
    }

    private static function tahapAwal(string $memo): string
    {
        return self::dariStatus(self::statusAnggaran($memo));
    }

    // Status anggaran dari tabel sumber. Memakai DB::table, jadi global scope 'unit' tidak ikut campur.
    // Melempar DomainException kalau memo tidak ada atau (untuk agenda) nomor memonya kosong.
    private static function statusAnggaran(string $memo): ?string
    {
        if (preg_match('/^entry-(\d+)$/', $memo, $m)) {
            $row = DB::table('memo_entries')->where('id', $m[1])->first();
            if (!$row) {
                throw new \DomainException('Memo tidak ditemukan.');
            }
            return $row->status_anggaran ?? null;
        }

        if (preg_match('/^agenda-(\d+)-(pns|non_pns)$/', $memo, $m)) {
            $pns = $m[2] === 'pns';
            $row = DB::table('agendas')->where('id', $m[1])->first();

            if (!$row || blank($row->{$pns ? 'nomor_memo_pns' : 'nomor_memo_non_pns'})) {
                throw new \DomainException('Memo tidak ditemukan.');
            }
            return $row->{$pns ? 'status_pns' : 'status_non_pns'} ?? null;
        }

        throw new \DomainException('Format memo tidak dikenal.');
    }
}