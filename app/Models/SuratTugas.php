<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratTugas extends Model
{
    protected $table = 'surat_tugas';

    protected $fillable = [
        'nomor', 'nomor_urut', 'tahun', 'kode_klasifikasi', 'status',
        'menimbang', 'dasar', 'untuk', 'kepada', 'tempat', 'tanggal',
        'jabatan_penandatangan', 'nama_penandatangan', 'nip_penandatangan',
        'arsip_surat_id', 'created_by', 'diterbitkan_at', 'dibatalkan_at', 'alasan_batal',
    ];

    protected $casts = [
        'dasar' => 'array',
        'untuk' => 'array',
        'tanggal' => 'date',
        'diterbitkan_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
    ];

    public const STATUS_LABEL = [
        'draf' => 'Draf',
        'terbit' => 'Terbit',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    public function peserta(): HasMany
    {
        return $this->hasMany(SuratTugasPegawai::class)->orderBy('urutan');
    }

    public function isDraf(): bool
    {
        return $this->status === 'draf';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draf' => 'bg-gray-100 text-gray-700',
            'terbit' => 'bg-blue-100 text-blue-700',
            'selesai' => 'bg-green-100 text-green-700',
            'batal' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getTanggalIndoAttribute(): string
    {
        return $this->tanggal->copy()->locale('id')->translatedFormat('j F Y');
    }

    /** Nomor untuk ditampilkan (draf: placeholder). */
    public function getNomorTampilAttribute(): string
    {
        return $this->nomor ?: '.../'.$this->kode_klasifikasi.'/'.$this->tanggal->year;
    }
}
