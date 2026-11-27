<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\DisposisiSurat;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Integrasi Google Calendar lewat Google Apps Script (web app), tanpa OAuth dan tanpa Google Cloud.
 *
 * - Script berjalan sebagai akun Google pusat (pemilik script).
 * - DISPOSISI: tiap disposisi dibuatkan event di kalender akun pusat, penerimanya
 *   (email di tabel users) jadi tamu. Google yang mengirim email undangan.
 * - SURAT MASUK BERJADWAL: satu event per surat, tamunya semua staf di unit pencatat
 *   (TU, Eselon II, dan admin tidak ikut; Eselon II sudah dapat event disposisi).
 * - Berkas surat (kalau ada dan bukan rahasia) diupload Apps Script ke Drive akun pusat
 *   dan dilampirkan ke event BARU. Update event tidak menyentuh lampiran.
 * - Pengingat di HP penerima mengikuti pengaturan kalender masing-masing.
 * - Semua panggilan dibungkus try/catch: kalau Google / Apps Script error, alur disposisi tetap jalan.
 * - Status sinkron terakhir disimpan di cache (tidak butuh tabel).
 */
class GoogleKalenderService
{
    private const CACHE_STATUS = 'google_kalender_status';

    public static function aktif(): bool
    {
        return filled(config('google_kalender.script_url'))
            && filled(config('google_kalender.script_rahasia'));
    }

    /** Status terakhir untuk kartu di halaman kalender. */
    public static function status(): array
    {
        $s = Cache::get(self::CACHE_STATUS, []);

        return [
            'terakhir_sinkron' => !empty($s['terakhir_sinkron']) ? Carbon::parse($s['terakhir_sinkron']) : null,
            'error' => $s['error'] ?? null,
        ];
    }

    private static function catatStatus(?string $error): void
    {
        $lama = Cache::get(self::CACHE_STATUS, []);

        Cache::forever(self::CACHE_STATUS, [
            'terakhir_sinkron' => $error === null ? now()->toIso8601String() : ($lama['terakhir_sinkron'] ?? null),
            'error' => $error,
        ]);
    }

    /** Cek koneksi ke Apps Script. Hasil: ['ok' => true, 'email' => akun pemilik script]. */
    public static function ping(): array
    {
        if (!self::aktif()) {
            return ['ok' => false, 'error' => 'GOOGLE_SCRIPT_URL / GOOGLE_SCRIPT_RAHASIA belum diisi di .env.'];
        }

        try {
            return self::panggil(['aksi' => 'ping']);
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /** Kirim satu perintah ke web app Apps Script, kembalikan JSON balasannya. */
    private static function panggil(array $payload): array
    {
        // Timeout 90 detik: berkas surat (sampai 10 MB) dikirim sebagai base64.
        $res = Http::timeout(90)
            ->asJson()
            ->post(config('google_kalender.script_url'), $payload + ['rahasia' => config('google_kalender.script_rahasia')]);

        $json = $res->json();
        if (!is_array($json)) {
            throw new \RuntimeException('Respons Apps Script tidak valid (HTTP ' . $res->status() . '). Cek URL web app dan pastikan aksesnya diatur "Anyone".');
        }

        return $json;
    }

    /** Daftar email uji (huruf kecil). Kosong = tidak ada pembatasan (mode produksi). */
    private static function daftarUji(): array
    {
        return collect(explode(',', (string) config('google_kalender.email_uji')))
            ->map(fn($e) => strtolower(trim($e)))
            ->filter()
            ->values()
            ->all();
    }

    /** Email penerima yang boleh diundang, atau null kalau tidak valid / di luar daftar uji. */
    private static function emailTamu(Disposisi $d): ?string
    {
        $email = strtolower(trim((string) $d->kepada?->email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $uji = self::daftarUji();
        if ($uji && !in_array($email, $uji, true)) {
            return null;
        }

        return $email;
    }

    // ================================================================ Event disposisi

    /**
     * Buat / perbarui event disposisi di kalender akun pusat, penerima jadi tamu.
     * $kirimEmail = true  -> event BARU mengirim email undangan (alur normal).
     * $kirimEmail = false -> senyap (dipakai saat sinkron massal supaya tidak membanjiri email).
     * Update event yang sudah ada senyap, kecuali $kabari = true (dipakai saat surat diubah).
     * Return true kalau berhasil, false kalau belum aktif / penerima tidak valid / gagal.
     */
    public static function sinkron(Disposisi $d, bool $kirimEmail = true, bool $kabari = false): bool
    {
        if (!self::aktif() || !$d->kepada_user_id) {
            return false;
        }

        $d->loadMissing(['surat', 'dari', 'kepada']);

        $email = self::emailTamu($d);
        if (!$email) {
            return false;
        }

        try {
            $body = self::isiEvent($d);
            $body['attendees'] = [array_filter(['email' => $email, 'displayName' => $d->kepada?->name])];
            $body['guestsCanSeeOtherGuests'] = false;
            $body['guestsCanInviteOthers'] = false;
            $body['guestsCanModify'] = false;

            $hasil = self::panggil([
                'aksi' => 'simpan',
                'event_id' => $d->google_event_id,
                'kirim_email' => $kirimEmail,
                // kabari = true: update event lama juga mengirim email "event diperbarui" dari Google (berisi apa yang berubah)
                'kabari' => $kabari,
                'body' => $body,
                // Berkas hanya dilampirkan saat event baru dibuat
                'berkas' => $d->google_event_id ? null : self::berkasEvent($d->surat, [$email]),
            ]);

            if (empty($hasil['ok']) || empty($hasil['id'])) {
                self::catatStatus('Gagal membuat event: ' . ($hasil['error'] ?? 'tidak ada respons'));

                return false;
            }

            $d->forceFill(['google_event_id' => $hasil['id']])->saveQuietly();
            self::catatStatus(null);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Sinkron Google Calendar disposisi #{$d->id} gagal: " . $e->getMessage());
            self::catatStatus('Koneksi ke Apps Script gagal: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Hapus event disposisi (dipakai saat surat dihapus). Penerima dapat email pembatalan dari Google.
     * Return true kalau event sudah tidak ada di Google (berhasil dihapus atau memang tidak punya event).
     */
    public static function hapus(Disposisi $d): bool
    {
        if (!self::aktif() || !$d->google_event_id) {
            return true;
        }

        try {
            // kirim_email: minta Apps Script memberi tahu tamu (sendUpdates "all") bahwa event dibatalkan
            $hasil = self::panggil(['aksi' => 'hapus', 'event_id' => $d->google_event_id, 'kirim_email' => true]);
            if (empty($hasil['ok'])) {
                Log::warning("Hapus event Google disposisi #{$d->id} ({$d->google_event_id}) gagal: " . ($hasil['error'] ?? '-'));

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("Hapus event Google disposisi #{$d->id} ({$d->google_event_id}) gagal: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Sinkron massal (tombol "Sinkronkan" admin): semua disposisi yang masih terbuka dibuatkan
     * / diperbarui eventnya, TANPA email. Bukan admin -> 0.
     */
    public static function sinkronSemuaMilik(User $user): int
    {
        if (!$user->isAdmin()) {
            return 0;
        }

        $n = 0;
        Disposisi::whereIn('status', Disposisi::TERBUKA)
            ->with(['surat', 'dari', 'kepada'])
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function ($d) use (&$n) {
                $n += self::sinkron($d, false) ? 1 : 0;
            });

        return $n;
    }

    /** Isi event Google Calendar untuk satu disposisi. */
    public static function isiEvent(Disposisi $d): array
    {
        $s = $d->surat;
        $tz = config('google_kalender.timezone', 'Asia/Jakarta');
        $tanggal = Carbon::parse($d->tanggal_kalender)->toDateString();
        $selesai = $d->status === 'selesai';
        $oper = $d->status === 'diteruskan';

        [$start, $end] = self::waktuEvent($s, $tanggal, $tz);

        $url = function_exists('route') ? route('disposisi.index', ['tampil' => 'kalender', 'tanggal' => $tanggal, 'buka' => $d->surat_id]) : null;

        $desk = array_filter([
            ...self::barisWaktu($s),
            $s?->tempat ? 'Tempat : ' . $s->tempat : null,
            $s ? 'Agenda : ' . ($s->isi_ringkas ?: $s->perihal) : null,
            $s?->link_daring ? "Link rapat daring :\n" . $s->link_daring : null,
            self::barisBerkas($s),
            "\nDari : " . ($d->dari?->name ?? '-'),
            $s ? 'Asal surat : ' . $s->asal_surat : null,
            $s ? 'Nomor surat : ' . $s->nomor_surat : null,
            'Instruksi : ' . (implode(', ', $d->instruksi_label) ?: '-'),
            $d->catatan ? 'Catatan : ' . $d->catatan : null,
            $d->batas_waktu ? 'Batas waktu : ' . $d->batas_waktu->format('d/m/Y') : null,
            $selesai ? 'Status : SELESAI' : null,
            $oper ? 'Status : sudah DITERUSKAN ke bawahan' : null,
            $url ? "\nBuka di SPJ MKDI: {$url}" : null,
        ]);

        $awalan = $selesai ? '✓ ' : ($oper ? '→ ' : '');
        $warna = $selesai ? '2' : ($oper ? '8' : ($s?->sifat === 'sangat_segera' ? '11' : ($s?->sifat === 'segera' ? '6' : '9')));

        return array_filter([
            'summary' => $awalan . self::judulSurat($s),
            'description' => implode("\n", $desk),
            'location' => $s?->tempat,
            'start' => $start,
            'end' => $end,
            'colorId' => $warna,
            // Kalender akun pusat dibuat senyap. Penerima memakai pengingat bawaan kalendernya sendiri.
            'reminders' => ['useDefault' => false, 'overrides' => []],
            'source' => $url ? ['title' => 'SPJ MKDI', 'url' => $url] : null,
            'extendedProperties' => ['private' => ['spj_disposisi_id' => (string) $d->id]],
        ], fn($v) => $v !== null && $v !== '');
    }

    // ================================================================ Event unit (surat masuk berjadwal)

    /**
     * Surat masuk berjadwal -> satu event di kalender akun pusat, tamunya semua staf di unit pencatat.
     * Syarat: jenis surat_masuk, ada tanggal kegiatan, sifat bukan rahasia.
     * Event baru mengirim email undangan, update berikutnya senyap.
     * Return true kalau berhasil, false kalau tidak memenuhi syarat / tidak ada tamu / gagal.
     */
    public static function sinkronUnit(DisposisiSurat $s, bool $kabari = false): bool
    {
        if (
            !self::aktif()
            || $s->jenis !== 'surat_masuk'
            || $s->sifat === 'rahasia'
            || !$s->tanggal_kegiatan
            || !$s->unit_id
        ) {
            return false;
        }

        $tamu = self::tamuUnit($s);
        if (!$tamu) {
            return false;
        }

        try {
            $body = self::isiEventUnit($s);
            $body['attendees'] = $tamu;
            $body['guestsCanSeeOtherGuests'] = false;
            $body['guestsCanInviteOthers'] = false;
            $body['guestsCanModify'] = false;

            $hasil = self::panggil([
                'aksi' => 'simpan',
                'event_id' => $s->google_event_id_unit,
                'kirim_email' => true,
                'kabari' => $kabari,
                'body' => $body,
                // Berkas hanya dilampirkan saat event baru dibuat
                'berkas' => $s->google_event_id_unit ? null : self::berkasEvent($s, array_column($tamu, 'email')),
            ]);

            if (empty($hasil['ok']) || empty($hasil['id'])) {
                self::catatStatus('Gagal membuat event unit: ' . ($hasil['error'] ?? 'tidak ada respons'));

                return false;
            }

            $s->forceFill(['google_event_id_unit' => $hasil['id']])->saveQuietly();
            self::catatStatus(null);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Sinkron Google Calendar unit surat #{$s->id} gagal: " . $e->getMessage());
            self::catatStatus('Koneksi ke Apps Script gagal: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Hapus event unit (dipakai saat surat dihapus / tidak lagi memenuhi syarat event unit).
     * Return true kalau event sudah tidak ada di Google (berhasil dihapus atau memang tidak punya event).
     */
    public static function hapusUnit(DisposisiSurat $s): bool
    {
        if (!self::aktif() || !$s->google_event_id_unit) {
            return true;
        }

        try {
            $hasil = self::panggil(['aksi' => 'hapus', 'event_id' => $s->google_event_id_unit, 'kirim_email' => true]);
            if (empty($hasil['ok'])) {
                Log::warning("Hapus event unit Google surat #{$s->id} ({$s->google_event_id_unit}) gagal: " . ($hasil['error'] ?? '-'));

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning("Hapus event unit Google surat #{$s->id} ({$s->google_event_id_unit}) gagal: " . $e->getMessage());

            return false;
        }
    }

    /**
     * Surat diubah -> samakan semua event Google dengan data terbaru:
     * - event unit: dibuat kalau baru memenuhi syarat, diperbarui kalau sudah ada,
     *   dihapus (tamu dapat email pembatalan) kalau tidak lagi memenuhi syarat
     *   (jadwal dikosongkan, jenis bukan surat masuk, atau sifat jadi rahasia);
     * - event tiap disposisi yang masih terbuka / sudah punya event: diperbarui.
     * Tamu dikabari lewat email "Event diperbarui" dari Google yang otomatis memuat apa yang berubah
     * (waktu, tempat, deskripsi, judul), kecuali penerima disposisi yang sudah selesai.
     * Berkas tidak dilampirkan ulang ke event yang sudah ada.
     *
     * @return array{unit:?string, disposisi:int, gagal:int}  unit: 'dibuat' | 'diperbarui' | 'dihapus' | null
     */
    public static function perbaruiSurat(DisposisiSurat $s): array
    {
        $hasil = ['unit' => null, 'disposisi' => 0, 'gagal' => 0];
        if (!self::aktif()) {
            return $hasil;
        }

        $s->refresh();

        // ---- event unit
        $layak = $s->jenis === 'surat_masuk' && $s->sifat !== 'rahasia' && $s->tanggal_kegiatan && $s->unit_id;
        if ($layak) {
            $ada = (bool) $s->google_event_id_unit;
            if (self::sinkronUnit($s, true)) {
                $hasil['unit'] = $ada ? 'diperbarui' : 'dibuat';
            } elseif ($ada || self::tamuUnit($s)) {
                $hasil['gagal']++;
            }
        } elseif ($s->google_event_id_unit) {
            if (self::hapusUnit($s)) {
                $s->forceFill(['google_event_id_unit' => null])->saveQuietly();
                $hasil['unit'] = 'dihapus';
            } else {
                $hasil['gagal']++;
            }
        }

        // ---- event disposisi
        $daftar = $s->disposisi()->with(['surat', 'dari', 'kepada'])->get();
        foreach ($daftar as $d) {
            if (!$d->google_event_id && !in_array($d->status, Disposisi::TERBUKA, true)) {
                continue;
            }
            // Disposisi yang sudah selesai tidak perlu dikabari lagi, cukup diperbarui senyap
            if (self::sinkron($d, true, $d->status !== 'selesai')) {
                $hasil['disposisi']++;
            } elseif ($d->google_event_id) {
                $hasil['gagal']++;
            }
        }

        return $hasil;
    }

    /**
     * Tamu event unit: staf di unit yang sama dengan unit pencatat surat.
     * TU, Eselon II, dan admin tidak ikut (Eselon II sudah dapat event disposisi).
     * Mode uji: hanya email di GOOGLE_EMAIL_UJI. Akun pusat tidak diundang ke eventnya sendiri.
     */
    private static function tamuUnit(DisposisiSurat $s): array
    {
        $uji = self::daftarUji();
        $pusat = strtolower(trim((string) config('google_kalender.akun_pusat')));

        return User::query()
            ->where('unit_id', $s->unit_id)
            ->whereNotIn('role', [
                User::ROLE_TU_UNIT,
                User::ROLE_TU_SEKRETARIAT,
                User::ROLE_ESELON2,
                User::ROLE_ADMIN,
                User::ROLE_SUPER_ADMIN,
            ])
            ->get(['id', 'name', 'email'])
            ->map(fn($u) => ['email' => strtolower(trim((string) $u->email)), 'displayName' => $u->name])
            ->filter(fn($t) => filter_var($t['email'], FILTER_VALIDATE_EMAIL)
                && $t['email'] !== $pusat
                && (!$uji || in_array($t['email'], $uji, true)))
            ->unique('email')
            ->values()
            ->all();
    }

    /** Baris "Berkas surat" di deskripsi event. Surat rahasia tidak pernah membawa link berkas. */
    private static function barisBerkas(?DisposisiSurat $s): ?string
    {
        if (!$s || $s->sifat === 'rahasia' || !$s->link_berkas) {
            return null;
        }

        return "Berkas surat (PDF) :\n" . $s->link_berkas;
    }

    /** Isi event unit. Tanpa link ke aplikasi (staf yang tidak terlibat tidak boleh membuka surat). */
    public static function isiEventUnit(DisposisiSurat $s): array
    {
        $tz = config('google_kalender.timezone', 'Asia/Jakarta');
        $tanggal = Carbon::parse($s->tanggal_kegiatan)->toDateString();

        [$start, $end] = self::waktuEvent($s, $tanggal, $tz);

        $desk = array_filter([
            ...self::barisWaktu($s),
            $s->tempat ? 'Tempat : ' . $s->tempat : null,
            'Agenda : ' . ($s->isi_ringkas ?: $s->perihal),
            $s->link_daring ? "Link rapat daring :\n" . $s->link_daring : null,
            self::barisBerkas($s),
            "\nAsal surat : " . $s->asal_surat,
            'Nomor surat : ' . $s->nomor_surat,
            'Surat masuk, untuk diketahui unit.',
        ]);

        return array_filter([
            'summary' => self::judulSurat($s),
            'description' => implode("\n", $desk),
            'location' => $s->tempat,
            'start' => $start,
            'end' => $end,
            'colorId' => '9',
            'reminders' => ['useDefault' => false, 'overrides' => []],
            'extendedProperties' => ['private' => ['spj_surat_id' => (string) $s->id]],
        ], fn($v) => $v !== null && $v !== '');
    }

    // ================================================================ Helper

    /**
     * Judul event (juga jadi subjek email undangan): "Asal surat – Perihal".
     * Mau perihal saja? Ganti isinya jadi: return Str::limit($perihal, 150);
     */
    private static function judulSurat(?DisposisiSurat $s): string
    {
        if (!$s) {
            return 'Surat';
        }

        $asal = trim((string) $s->asal_surat);
        $perihal = trim((string) $s->perihal);

        return $asal !== ''
            ? Str::limit($asal, 50) . ' – ' . Str::limit($perihal, 100)
            : Str::limit($perihal, 150);
    }

    /**
     * Baris tanggal dan jam untuk deskripsi event, format Indonesia seperti di form:
     *   Tanggal : Kamis, 26 November 2026
     *   Jam mulai (WIB) : 12.30
     *   Jam selesai (WIB) : 13.30
     * Zona waktu event = Asia/Jakarta (WIB).
     */
    private static function barisWaktu(?DisposisiSurat $s): array
    {
        if (!$s?->tanggal_kegiatan) {
            return [];
        }

        $tgl = Carbon::parse($s->tanggal_kegiatan)->locale('id')->translatedFormat('l, j F Y');
        $baris = ["Tanggal : {$tgl}"];

        if ($s->waktu_mulai) {
            $baris[] = 'Jam mulai (WIB) : ' . str_replace(':', '.', substr((string) $s->waktu_mulai, 0, 5));
            $baris[] = 'Jam selesai (WIB) : ' . ($s->waktu_selesai
                ? str_replace(':', '.', substr((string) $s->waktu_selesai, 0, 5))
                : 'Selesai');
        } else {
            $baris[] = 'Jam : Seharian';
        }

        return $baris;
    }

    /**
     * Berkas surat untuk dilampirkan ke event (diupload Apps Script ke Drive akun pusat).
     * Null kalau tidak ada berkas, suratnya rahasia, atau file tidak ditemukan.
     */
    private static function berkasEvent(?DisposisiSurat $s, array $penerima): ?array
    {
        if (!$s || $s->sifat === 'rahasia' || !$s->file_path || !Storage::disk('local')->exists($s->file_path)) {
            return null;
        }

        return [
            'nama' => $s->file_name ?: basename($s->file_path),
            'mime' => Storage::disk('local')->mimeType($s->file_path) ?: 'application/octet-stream',
            'base64' => base64_encode(Storage::disk('local')->get($s->file_path)),
            'penerima' => $penerima,
        ];
    }

    /** Waktu mulai dan selesai event: berjam kalau surat punya tanggal + jam mulai, selain itu seharian. */
    private static function waktuEvent(?DisposisiSurat $s, string $tanggal, string $tz): array
    {
        if ($s?->tanggal_kegiatan && $s->waktu_mulai) {
            $mulai = Carbon::parse($tanggal . ' ' . substr((string) $s->waktu_mulai, 0, 5), $tz);
            $akhir = $s->waktu_selesai
                ? Carbon::parse($tanggal . ' ' . substr((string) $s->waktu_selesai, 0, 5), $tz)
                : $mulai->copy()->addHour();
            if ($akhir->lte($mulai)) {
                $akhir = $mulai->copy()->addHour();
            }

            return [
                ['dateTime' => $mulai->format('Y-m-d\TH:i:s'), 'timeZone' => $tz],
                ['dateTime' => $akhir->format('Y-m-d\TH:i:s'), 'timeZone' => $tz],
            ];
        }

        return [
            ['date' => $tanggal],
            ['date' => Carbon::parse($tanggal)->addDay()->toDateString()],
        ];
    }
}
