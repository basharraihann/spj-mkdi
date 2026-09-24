<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbmRate extends Model
{
    protected $fillable = ['provinsi', 'uh_biasa'];

    protected $casts = [
        'uh_biasa' => 'decimal:2',
    ];

    /**
     * UH Biasa 60% gak disimpan di DB — selalu dihitung 60% dari UH Biasa
     * provinsi ybs, dibulatkan ke rupiah terdekat.
     */
    public function getUhBiasa60Attribute(): int
    {
        return (int) round($this->uh_biasa * 0.6);
    }

    /**
     * Ambil rate satu provinsi, dipakai buat auto-patok pas isi rincian biaya.
     * Return null kalau provinsi belum ada di master (misal data lama yg
     * tujuan-nya teks bebas, sebelum fitur ini ada).
     */
    public static function forProvinsi(?string $provinsi): ?self
    {
        if (! $provinsi) {
            return null;
        }

        return static::where('provinsi', $provinsi)->first();
    }
}
