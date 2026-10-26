<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTugasKontrol extends Model
{
    protected $table = 'surat_tugas_kontrol';

    protected $fillable = ['surat_tugas_id', 'urutan', 'tahap', 'petugas_id', 'status', 'catatan', 'acted_at'];

    protected $casts = ['acted_at' => 'datetime'];

    /** Urutan baku tahapan lembar kontrol. */
    public const TAHAP = [
        1 => 'konseptor',
        2 => 'pemeriksa',
        3 => 'persetujuan_es2',
        4 => 'kontrol_sesdep',
        5 => 'persetujuan_sesdep',
    ];

    public const TAHAP_LABEL = [
        'konseptor' => 'Konseptor (Admin TU)',
        'pemeriksa' => 'Pemeriksa',
        'persetujuan_es2' => 'Persetujuan Eselon 2',
        'kontrol_sesdep' => 'Kontrol & Penomoran (Admin Sesdep)',
        'persetujuan_sesdep' => 'Persetujuan Sesdep',
    ];

    public const STATUS_LABEL = [
        'menunggu' => 'Menunggu',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function getTahapLabelAttribute(): string
    {
        return self::TAHAP_LABEL[$this->tahap] ?? $this->tahap;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? $this->status;
    }
}
