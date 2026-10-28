<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTugasKontrol extends Model
{
    protected $table = 'surat_tugas_kontrol';

    protected $fillable = ['surat_tugas_id', 'urutan', 'tahap', 'petugas_id', 'status', 'catatan', 'acted_at'];

    protected $casts = ['acted_at' => 'datetime'];

    /**
     * Urutan baku tahapan lembar kontrol.
     * Tahap 2 dilewati bila Eselon 2 unit pembuat = Sesdep sendiri (nomor urut tetap, boleh ada celah).
     */
    public const TAHAP = [
        1 => 'konseptor',
        2 => 'persetujuan_es2',
        3 => 'kontrol_sesdep',
        4 => 'persetujuan_sesdep',
    ];

    /** Tahap yang mengambil nomor saat disetujui. */
    public const TAHAP_NOMOR = 'kontrol_sesdep';

    public const TAHAP_LABEL = [
        'konseptor' => 'Konseptor (TU)',
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