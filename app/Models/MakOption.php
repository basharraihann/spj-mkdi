<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MakOption extends Model
{
    protected $table = 'mak_options';

    protected $fillable = [
        'mak',
        'uraian_giat',
        'uraian_komponen',
        'uraian_akun_ap',
        'uraian_belanja',
        'pagu',
        'realisasi',
        'sisa',
        'periode',
        'per_tanggal',
        'is_active',
        'sumber',
    ];

    protected $casts = ['is_active' => 'boolean'];
}