<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengRiilRate extends Model
{
    protected $fillable = ['kategori', 'tujuan', 'rate_one_way'];

    protected $casts = [
        'rate_one_way' => 'decimal:2',
    ];

    /**
     * Nominal yang dipakai di aplikasi (PP = pergi + pulang), selalu 2x
     * rate_one_way. Ini yang dipatok server-side pas pesertaStore(), bukan
     * angka yang dikirim dari client.
     */
    public function getRatePpAttribute(): int
    {
        return (int) round($this->rate_one_way * 2);
    }
}
