<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function disposisi()
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    // Accessor otomatis untuk embed iframe preview Google Drive
    public function getEmbedDriveUrlAttribute()
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