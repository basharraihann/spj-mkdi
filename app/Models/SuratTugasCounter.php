<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratTugasCounter extends Model
{
    protected $table = 'surat_tugas_counters';
    protected $fillable = ['tahun', 'last_number'];
}
