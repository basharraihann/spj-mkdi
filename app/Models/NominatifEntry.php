<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class NominatifEntry extends Model
{
    use BelongsToUnit;

    protected $table = 'nominatif_entries';

    protected $fillable = [
        'unit_id',          // <- baru
        'uraian_kegiatan',
        'tanggal',
        'provinsi',
        'kota',
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