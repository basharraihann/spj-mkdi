<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tusi extends Model
{
    protected $table = 'tusi';

    protected $fillable = ['kode', 'nama', 'deskripsi', 'urutan', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function suratTugas(): HasMany
    {
        return $this->hasMany(SuratTugas::class);
    }

    public function scopeAktif($q)
    {
        return $q->where('aktif', true);
    }

    public function scopeUrut($q)
    {
        return $q->orderBy('urutan')->orderBy('nama');
    }
}
