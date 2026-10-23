<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluars';

    protected $fillable = [
        'jenis_dokumen',
        'nomor_surat',
        'sifat',
        'hal',
        'tanggal_surat',
        'tujuan',
        'dari',
        'isi_payload',
        'tembusan',
        'created_by',
        'status_persetujuan',
        'file_scan_ttd',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'isi_payload'    => 'array',
        'tembusan'        => 'array',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNamaJenisAttribute(): string
    {
        return match ($this->jenis_dokumen) {
            'nota_dinas' => 'Nota Dinas',
            'undangan' => 'Undangan / Surat Dinas',
            'telaah_staf' => 'Telaah Staf',
            'notula' => 'Notula Rapat',
            'surat_pernyataan' => 'Surat Pernyataan',
            default => 'Dokumen',
        };
    }
}