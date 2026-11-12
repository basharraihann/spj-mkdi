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
    ];

    protected $guarded = [];

    protected $casts = [
        'tanggal_memo' => 'date',
    ];

    public function getJenisLabelAttribute(): string
    {
        return self::JENIS[$this->jenis_memo]
            ?? ucfirst(str_replace('_', ' ', (string) $this->jenis_memo));
    }

    /**
     * Teks "Hal" di PDF: tipe pembayaran + jenis memo.
     * Contoh: "Permintaan Tambahan Uang Persediaan (TUP) Konsumsi"
     */
    public function getHalMemoAttribute(): string
    {
        $tipe = self::TIPE_PEMBAYARAN[$this->tipe_pembayaran ?? 'ls']
            ?? self::TIPE_PEMBAYARAN['ls'];
        $jenis = self::JENIS_HAL[$this->jenis_memo] ?? null;

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