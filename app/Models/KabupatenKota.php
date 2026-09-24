<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KabupatenKota extends Model
{
    protected $table = 'kabupaten_kota';

    protected $fillable = ['provinsi', 'nama'];

    /**
     * Daftar kab/kota buat satu provinsi, urut nama. Dipakai buat isi
     * dropdown Kab/Kota di form Buat/Edit Agenda setelah provinsi dipilih.
     */
    public static function forProvinsi(?string $provinsi)
    {
        if (! $provinsi) {
            return collect();
        }

        return static::where('provinsi', $provinsi)->orderBy('nama')->pluck('nama');
    }
}
