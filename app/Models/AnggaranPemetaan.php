<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggaranPemetaan extends Model
{
    protected $table = 'anggaran_pemetaan';

    protected $fillable = ['keg_kode', 'ro_kode', 'komp_kode', 'sub_kode', 'unit', 'sub_unit'];
}
