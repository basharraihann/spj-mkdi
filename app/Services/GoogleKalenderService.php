<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\GoogleKalenderAkun;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi Google Calendar lewat REST API (tanpa package tambahan, cukup HTTP client Laravel).
 *
 * - Tiap user menghubungkan akun Google-nya sendiri (OAuth 2.0, scope calendar.events).
 * - Setiap disposisi yang DITERIMA user dibuatkan event di Google Calendar-nya,
 *   lengkap dengan pengingat (notifikasi HP / email bawaan Google Calendar).
 * - Semua panggilan ke Google dibungkus try/catch: kalau Google error / belum dihubungkan,
 *   alur disposisi di aplikasi tetap jalan.
 */
class GoogleKalenderService
{
    private const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';
    private const USERINFO_URL = 'https://openidconnect.googleapis.com/v1/userinfo';
    private const API = 'https://www.googleapis.com/calendar/v3';

    public static function aktif(): bool
    {
        return filled(config('google_kalender.client_id'))
            && filled(config('google_kalender.client_secret'))
            && filled(self::redirectUri());
    }

    public static function redirectUri(): ?string
    {
        return config('google_kalender.redirect') ?: (function_exists('route') ? route('kalender.google.callback') : null);
    }

    // ================================================================ OAuth

    public static function urlIzin(string $state): string
    {
        return self::AUTH_URL . '?' . http_build_query([
            'client_id' => config('google_kalender.client_id'),
            'redirect_uri' => self::redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', config('google_kalender.scopes')),
            'access_type' => 'offline',          // supaya dapat refresh_token
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ]);
    }

    /** Tukar kode OAuth -> token, simpan untuk user. */
    public static function simpanDariKode(User $user, string $code): GoogleKalenderAkun
    {
        $res = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
            'code' => $code,
            'client_id' => config('google_kalender.client_id'),
            'client_secret' => config('google_kalender.client_secret'),
            'redirect_uri' => self::redirectUri(),
            'grant_type' => 'authorization_code',
        ]);

        if (!$res->successful()) {
            throw new \RuntimeException('Google menolak kode izin: ' . ($res->json('error_description') ?? $res->body()));
        }

        $t = $res->json();
        $email = null;
        try {
            $email = Http::withToken($t['access_token'])->timeout(10)->get(self::USERINFO_URL)->json('email');
        } catch (\Throwable) {
        }

        $akun = GoogleKalenderAkun::firstOrNew(['user_id' => $user->id]);
        $akun->fill([
            'google_email' => $email,
            'access_token' => $t['access_token'],
            'expires_at' => now()->addSeconds(max(60, (int) ($t['expires_in'] ?? 3600) - 60)),
            'calendar_id' => $akun->calendar_id ?: 'primary',
            'error_terakhir' => null,
        ]);
        // refresh_token hanya dikirim saat izin pertama / prompt=consent; jangan timpa dengan null
        if (!empty($t['refresh_token'])) {
            $akun->refresh_token = $t['refresh_token'];
        }
        $akun->save();

        return $akun;
    }

    public static function putuskan(GoogleKalenderAkun $akun): void
    {
        try {
            $token = $akun->refresh_token ?: $akun->access_token;
            if ($token) {
                Http::asForm()->timeout(10)->post(self::REVOKE_URL, ['token' => $token]);
            }
        } catch (\Throwable $e) {
            Log::info('Google revoke gagal: ' . $e->getMessage());
        }

        $akun->delete();
    }

    /** Access token yang masih berlaku (refresh otomatis). null = tidak bisa dipakai. */
    private static function token(GoogleKalenderAkun $akun): ?string
    {
        if ($akun->access_token && $akun->expires_at && $akun->expires_at->isFuture()) {
            return $akun->access_token;
        }

        if (!$akun->refresh_token) {
            return null;
        }

        $res = Http::asForm()->timeout(15)->post(self::TOKEN_URL, [
            'client_id' => config('google_kalender.client_id'),
            'client_secret' => config('google_kalender.client_secret'),
            'refresh_token' => $akun->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$res->successful()) {
            // invalid_grant = izin dicabut user di akun Google
            $akun->update(['error_terakhir' => 'Token Google tidak berlaku lagi. Hubungkan ulang Google Calendar.']);

            return null;
        }

        $akun->update([
            'access_token' => $res->json('access_token'),
            'expires_at' => now()->addSeconds(max(60, (int) $res->json('expires_in', 3600) - 60)),
            'error_terakhir' => null,
        ]);

        return $akun->access_token;
    }

    // ================================================================ Event

    /**
     * Buat / perbarui event disposisi di Google Calendar penerimanya.
     * Return true kalau berhasil, false kalau penerima belum menghubungkan Google / gagal.
     */
    public static function sinkron(Disposisi $d): bool
    {
        if (!self::aktif() || !$d->kepada_user_id) {
            return false;
        }

        $akun = GoogleKalenderAkun::where('user_id', $d->kepada_user_id)->first();
        if (!$akun) {
            return false;
        }

        try {
            $token = self::token($akun);
            if (!$token) {
                return false;
            }

            $d->loadMissing(['surat', 'dari']);
            $body = self::isiEvent($d);
            $kal = rawurlencode($akun->calendar_id ?: 'primary');
            $http = Http::withToken($token)->acceptJson()->timeout(15);

            $res = $d->google_event_id
                ? $http->patch(self::API . "/calendars/{$kal}/events/" . rawurlencode($d->google_event_id), $body)
                : null;

            // Event lama dihapus manual di Google -> buat baru
            if (!$res || in_array($res->status(), [404, 410], true)) {
                $res = $http->post(self::API . "/calendars/{$kal}/events", $body);
            }

            if (!$res->successful()) {
                $akun->update(['error_terakhir' => 'Gagal membuat event: ' . ($res->json('error.message') ?? $res->status())]);

                return false;
            }

            $d->forceFill(['google_event_id' => $res->json('id')])->saveQuietly();
            $akun->update(['terakhir_sinkron' => now(), 'error_terakhir' => null]);

            return true;
        } catch (\Throwable $e) {
            Log::warning("Sinkron Google Calendar disposisi #{$d->id} gagal: " . $e->getMessage());
            $akun->update(['error_terakhir' => 'Koneksi ke Google gagal: ' . $e->getMessage()]);

            return false;
        }
    }

    /** Hapus event (dipakai saat surat dihapus). */
    public static function hapus(Disposisi $d): void
    {
        if (!self::aktif() || !$d->google_event_id || !$d->kepada_user_id) {
            return;
        }

        $akun = GoogleKalenderAkun::where('user_id', $d->kepada_user_id)->first();
        if (!$akun) {
            return;
        }

        try {
            if ($token = self::token($akun)) {
                $kal = rawurlencode($akun->calendar_id ?: 'primary');
                Http::withToken($token)->timeout(10)
                    ->delete(self::API . "/calendars/{$kal}/events/" . rawurlencode($d->google_event_id));
            }
        } catch (\Throwable $e) {
            Log::info("Hapus event Google disposisi #{$d->id} gagal: " . $e->getMessage());
        }
    }

    /** Sinkronkan semua disposisi terbuka milik user (dipakai setelah menghubungkan akun). */
    public static function sinkronSemuaMilik(User $user): int
    {
        $n = 0;
        Disposisi::where('kepada_user_id', $user->id)
            ->whereIn('status', ['baru', 'dibaca', 'diteruskan'])
            ->with(['surat', 'dari'])
            ->orderBy('id')
            ->limit(200)
            ->get()
            ->each(function ($d) use (&$n) {
                $n += self::sinkron($d) ? 1 : 0;
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


        if ($s?->tanggal_kegiatan && $s->waktu_mulai) {
            $mulai = Carbon::parse($tanggal . ' ' . substr((string) $s->waktu_mulai, 0, 5), $tz);
            $akhir = $s->waktu_selesai
                ? Carbon::parse($tanggal . ' ' . substr((string) $s->waktu_selesai, 0, 5), $tz)
                : $mulai->copy()->addHour();
            if ($akhir->lte($mulai)) {
                $akhir = $mulai->copy()->addHour();
            }
            $start = ['dateTime' => $mulai->format('Y-m-d\TH:i:s'), 'timeZone' => $tz];
            $end = ['dateTime' => $akhir->format('Y-m-d\TH:i:s'), 'timeZone' => $tz];
            $menit = config('google_kalender.pengingat_berjam', [1440, 60]);
        } else {
            $start = ['date' => $tanggal];
            $end = ['date' => Carbon::parse($tanggal)->addDay()->toDateString()];
            $menit = config('google_kalender.pengingat_seharian', [960]);
        }

        $url = function_exists('route') ? route('kalender.index', ['tanggal' => $tanggal, 'buka' => $d->surat_id]) : null;

        $desk = array_filter([
            'Dari: ' . ($d->dari?->name ?? '-'),
            $s ? 'Nomor surat: ' . $s->nomor_surat : null,
            $s ? 'Asal surat: ' . $s->asal_surat : null,
            $s?->link_daring ? 'Link daring: ' . $s->link_daring : null,
            'Instruksi: ' . (implode(', ', $d->instruksi_label) ?: '-'),
            $d->catatan ? 'Catatan: ' . $d->catatan : null,
            $d->batas_waktu ? 'Batas waktu: ' . $d->batas_waktu->format('d/m/Y') : null,
            $selesai ? 'Status: SELESAI' : null,
            $url ? "\nBuka di SPJ MKDI: {$url}" : null,
        ]);

        return array_filter([
            'summary' => ($selesai ? '✓ ' : '') . '[Disposisi] ' . ($s?->perihal ? \Illuminate\Support\Str::limit($s->perihal, 120) : 'Surat'),
            'description' => implode("\n", $desk),
            'location' => $s?->tempat,
            'start' => $start,
            'end' => $end,
            'colorId' => $selesai ? '2' : ($s?->sifat === 'sangat_segera' ? '11' : ($s?->sifat === 'segera' ? '6' : '9')),
            'reminders' => [
                'useDefault' => false,
                'overrides' => $selesai ? [] : collect($menit)->map(fn($m) => ['method' => 'popup', 'minutes' => (int) $m])->values()->all(),
            ],
            'source' => $url ? ['title' => 'SPJ MKDI', 'url' => $url] : null,
            'extendedProperties' => ['private' => ['spj_disposisi_id' => (string) $d->id]],
        ], fn($v) => $v !== null && $v !== '');
    }
}
