<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SbmFlatRate extends Model
{
    protected $fillable = ['uh_fullday', 'uh_fullboard'];

    protected $casts = [
        'uh_fullday' => 'decimal:2',
        'uh_fullboard' => 'decimal:2',
    ];

    /**
     * Selalu ada tepat 1 baris (id = 1). Dipakai gantiin config(), biar
     * gampang diedit dari halaman admin nanti tanpa deploy ulang.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1], [
            'uh_fullday' => 95000,
            'uh_fullboard' => 130000,
        ]);
    }
}