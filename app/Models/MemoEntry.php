<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class MemoEntry extends Model
{
    use BelongsToUnit;

    /**
     * Daftar jenis memo mandiri: key = nilai di kolom jenis_memo, value = label tampilan.
     * Nambah jenis baru cukup di sini (lalu tambah juga di JENIS_HAL).
     */
    public const JENIS = [
        'konsumsi' => 'Konsumsi',
        'honorarium' => 'Honorarium',
        'atk' => 'ATK',
        'seminar_kit' => 'Seminar Kit',
        'sewa_ruangan' => 'Sewa Ruangan',
        'fullboard_meeting' => 'Fullboard Meeting',
        'fullday_meeting' => 'Fullday Meeting',
        'perdin' => 'Perjalanan Dinas',
        // Tampungan untuk jenis yang diketik manual. Teks aslinya ada di kolom jenis_manual.
        'lainnya' => 'Lainnya',
    ];

    /**
     * Tipe pembayaran: key = nilai kolom tipe_pembayaran,
     * value = teks yang muncul di awal "Hal" pada PDF.
     */
    public const TIPE_PEMBAYARAN = [
        'ls' => 'Permintaan Pembayaran Langsung (LS)',
        'up' => 'Permintaan Uang Persediaan (UP)',
        'gup' => 'Permintaan Ganti Uang Persediaan (GUP)',
        'tup' => 'Permintaan Tambahan Uang Persediaan (TUP)',
        'ptup' => 'Permintaan Pertanggungjawaban Tambahan Uang Persediaan (PTUP)',
    ];

    /**
     * Teks di belakang tipe pembayaran pada "Hal".
     */
    public const JENIS_HAL = [
        'konsumsi' => 'Konsumsi',
        'honorarium' => 'Honorarium',
        'atk' => 'Alat Tulis Kantor (ATK)',
        'seminar_kit' => 'Seminar Kit',
        'sewa_ruangan' => 'Sewa Ruangan',
        'fullboard_meeting' => 'Fullboard Meeting',
        'fullday_meeting' => 'Fullday Meeting',
        'perdin' => 'Perjalanan Dinas',
        'lainnya' => 'Lainnya',
    ];

    protected $guarded = [];

    protected $casts = [
        'tanggal_memo' => 'date',
    ];

    /**
     * Pisahkan isian jenis memo (boleh diketik manual) menjadi [kategori, teks manual].
     * - Cocok dengan daftar (key atau label) -> kategori itu, teks manual kosong.
     * - Tidak cocok -> kategori "lainnya", teks aslinya disimpan sebagai teks manual.
     *
     * @return array{0: string, 1: ?string}
     */
    public static function pisahJenis(?string $nilai): array
    {
        $nilai = trim(preg_replace('/\s+/u', ' ', (string) $nilai));
        if ($nilai === '') {
            return ['', null];
        }

        $cari = mb_strtolower($nilai);
        foreach (self::JENIS as $key => $label) {
            if ($cari === $key || $cari === mb_strtolower($label) || $cari === mb_strtolower(self::JENIS_HAL[$key] ?? '')) {
                return [$key, null];
            }
        }

        return ['lainnya', $nilai];
    }

    /**
     * Rapikan isian tipe pembayaran (boleh diketik manual).
     * Cocok dengan key ("ls"), kode ("LS"), atau teks lengkap -> key. Lainnya disimpan apa adanya.
     */
    public static function normalisasiTipe(?string $nilai): string
    {
        $nilai = trim(preg_replace('/\s+/u', ' ', (string) $nilai));
        if ($nilai === '') {
            return '';
        }

        $cari = mb_strtolower($nilai);
        foreach (self::TIPE_PEMBAYARAN as $key => $teks) {
            if ($cari === $key || $cari === mb_strtolower($teks)) {
                return $key;
            }
        }

        return $nilai;
    }

    public function getJenisLabelAttribute(): string
    {
        // Ini label KATEGORI (badge di daftar, monitoring, filter): jenis yang diketik manual
        // tampil sebagai "Lainnya". Teks aslinya hanya dipakai di dokumen memo (lihat getHalMemoAttribute).
        return self::JENIS[$this->jenis_memo]
            ?? ucfirst(str_replace('_', ' ', (string) $this->jenis_memo));
    }

    /**
     * Teks "Hal" di PDF: tipe pembayaran + jenis memo.
     * Contoh: "Permintaan Tambahan Uang Persediaan (TUP) Konsumsi"
     */
    public function getHalMemoAttribute(): string
    {
        // Nilai yang diketik manual (tidak ada di daftar) dipakai apa adanya.
        $tipe = self::TIPE_PEMBAYARAN[$this->tipe_pembayaran ?? 'ls']
            ?? (filled($this->tipe_pembayaran) ? $this->tipe_pembayaran : self::TIPE_PEMBAYARAN['ls']);
        $jenis = ($this->jenis_memo === 'lainnya' && filled($this->jenis_manual))
            ? $this->jenis_manual
            : (self::JENIS_HAL[$this->jenis_memo] ?? $this->jenis_memo);

        return trim($tipe . ' ' . $jenis);
    }

    public function pic()
    {
        return $this->belongsTo(Pegawai::class, 'pic_id');
    }

    public function ppk()
    {
        return $this->belongsTo(Pegawai::class, 'ppk_id');
    }

    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(Pegawai::class, 'penanggung_jawab_id');
    }

    public function petugasVerifikasi()
    {
        return $this->belongsTo(Pegawai::class, 'petugas_verifikasi_id');
    }
}