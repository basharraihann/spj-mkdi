<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratMasuk extends Model
{
    use HasFactory;

    protected $table = 'surat_masuk';

    protected $fillable = [
        'nomor_agenda',
        'nomor_surat',
        'asal_surat',
        'perihal',
        'tanggal_surat',
        'tanggal_diterima',
        'drive_link',
        'created_by_id',
        'status',
    ];

    protected $casts = [
        'tanggal_surat'    => 'date',
        'tanggal_diterima' => 'date',
    ];

    /**
     * Relasi ke seluruh rantai Disposisi terkait Surat Masuk ini.
     */
    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    /**
     * Relasi ke User yang mencatat Surat Masuk ini.
     * Nama method disesuaikan dengan panggilan `createdBy` pada Controller.
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Alias relasi ke pembuat (jika ada view lama yang memanggil $surat->pembuat).
     */
    public function pembuat(): BelongsTo
    {
        return $this->createdBy();
    }

    /**
     * Accessor otomatis untuk embed iframe preview Google Drive.
     */
    public function getEmbedDriveUrlAttribute(): ?string
    {
        if (!$this->drive_link) {
            return null;
        }

        if (preg_match('/(?:d\/|id=)([\w-]+)/', $this->drive_link, $matches)) {
            return "https://drive.google.com/file/d/{$matches[1]}/preview";
        }

        return $this->drive_link;
    }
}