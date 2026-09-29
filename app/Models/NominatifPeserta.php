<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominatifPeserta extends Model
{
    protected $table = 'nominatif_peserta';

    protected $fillable = [
        'nominatif_entry_id',
        'urutan',
        'nama',
        'npwp',
        'sebagai',
        'instansi',
        'golongan',
        'jabatan',
        'honor',
        'oj',
        'pajak_persen',
    ];

    protected $casts = [
        'honor' => 'integer',
        'oj' => 'integer',
        'pajak_persen' => 'float',
    ];

    public function entry()
    {
        return $this->belongsTo(NominatifEntry::class, 'nominatif_entry_id');
    }

    // Bruto = honor per OJ x jumlah OJ
    public function getBrutoAttribute(): int
    {
        return (int) ($this->honor * $this->oj);
    }

    // Pajak = bruto x persen pajak, dibulatkan ke rupiah
    public function getPajakAttribute(): int
    {
        return (int) round($this->bruto * $this->pajak_persen / 100);
    }

    public function getNettoAttribute(): int
    {
        return $this->bruto - $this->pajak;
    }
}