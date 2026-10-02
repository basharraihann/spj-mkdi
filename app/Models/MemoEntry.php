<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class MemoEntry extends Model
{
    use BelongsToUnit;

    /**
     * Daftar jenis memo mandiri: key = nilai di kolom jenis_memo, value = label tampilan.
     * Nambah jenis baru cukup di sini (lalu tambah case di match "Hal" pada MemoController::pdf()).
     */
    public const JENIS = [
        'konsumsi' => 'Konsumsi',
        'honorarium' => 'Honorarium',
        'atk' => 'ATK',
        'seminar_kit' => 'Seminar Kit',
        'sewa_ruangan' => 'Sewa Ruangan',
        'fullboard_meeting' => 'Fullboard Meeting',
        'fullday_meeting' => 'Fullday Meeting',
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