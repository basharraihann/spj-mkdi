<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanSt extends Model
{
    protected $table = 'laporan_st';

    protected $fillable = [
        'surat_tugas_id',
        'status',
        'notulensi',
        'keputusan',
        'diperbarui_oleh',
        'dikirim_oleh',
        'dikirim_at',
    ];

    protected $casts = [
        'dikirim_at' => 'datetime',
    ];

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class);
    }

    public function tindakLanjut(): HasMany
    {
        return $this->hasMany(LaporanStTindakLanjut::class)->orderBy('urutan');
    }

    public function dikirimOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikirim_oleh');
    }

    public function isDikirim(): bool
    {
        return $this->status === 'dikirim';
    }
}
