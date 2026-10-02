<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NominatifItem extends Model
{
    protected $table = 'nominatif_items';

    protected $fillable = [
        'nominatif_entry_id',
        'urutan',
        'uraian',
        'harga',
        'jumlah',
        'satuan',
        'ppn_persen',
        'pph22_persen',
        'pph23_persen',
    ];

    protected $casts = [
        'harga' => 'integer',
        'jumlah' => 'float',
        'ppn_persen' => 'float',
        'pph22_persen' => 'float',
        'pph23_persen' => 'float',
    ];

    public function entry()
    {
        return $this->belongsTo(NominatifEntry::class, 'nominatif_entry_id');
    }

    // Subtotal = harga x jumlah
    public function getSubtotalAttribute(): int
    {
        return (int) round($this->harga * $this->jumlah);
    }

    public function getPpnAttribute(): int
    {
        return (int) round($this->subtotal * $this->ppn_persen / 100);
    }

    public function getPph22Attribute(): int
    {
        return (int) round($this->subtotal * $this->pph22_persen / 100);
    }

    public function getPph23Attribute(): int
    {
        return (int) round($this->subtotal * $this->pph23_persen / 100);
    }

    // Total = subtotal + PPN - PPh 22 - PPh 23
    public function getTotalAttribute(): int
    {
        return $this->subtotal + $this->ppn - $this->pph22 - $this->pph23;
    }
}