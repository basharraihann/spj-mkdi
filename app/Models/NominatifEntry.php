<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominatifEntry extends Model
{
    protected $table = 'nominatif_entries';

    protected $fillable = [
        'uraian_kegiatan',
        'tanggal',
        'provinsi',   // baru
        'kota',       // baru
        'ppk_id',
        'bendahara_id',
    ];
    protected $casts = [
        'tanggal' => 'date',
    ];

    public function peserta()
    {
        return $this->hasMany(NominatifPeserta::class, 'nominatif_entry_id')->orderBy('urutan');
    }

    public function ppk()
    {
        return $this->belongsTo(Pegawai::class, 'ppk_id');
    }

    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }

    public function totalBruto(): int
    {
        return (int) $this->peserta->sum(fn($p) => $p->bruto);
    }

    public function totalPajak(): int
    {
        return (int) $this->peserta->sum(fn($p) => $p->pajak);
    }

    public function totalNetto(): int
    {
        return $this->totalBruto() - $this->totalPajak();
    }
}