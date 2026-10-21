<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_masuk_id',
        'parent_dispo_id',
        'pengirim_id',
        'penerima_id',
        'instruksi',
        'tenggat_waktu',
        'sifat',
        'perlu_st',
        'is_tembusan',
        'status',
        'dibaca_at',
    ];

    protected $casts = [
        'tenggat_waktu' => 'date',
        'dibaca_at'     => 'datetime',
        'perlu_st'      => 'boolean',
        'is_tembusan'   => 'boolean',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function parent()
    {
        return $this->belongsTo(Disposisi::class, 'parent_dispo_id');
    }

    public function children()
    {
        return $this->hasMany(Disposisi::class, 'parent_dispo_id');
    }

    public function suratTugas()
    {
        return $this->hasMany(SuratTugas::class, 'disposisi_id');
    }

    public function notaDinas()
    {
        return $this->hasOne(NotaDinas::class, 'disposisi_id');
    }
}