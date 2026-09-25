<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemoEntry extends Model
{
    protected $guarded = [];

    protected $casts = [
        'tanggal_memo' => 'date',
    ];

    public function pic()
    {
        return $this->belongsTo(Pegawai::class, 'pic_id');
    }

    public function ppk()
    {
        return $this->belongsTo(Pegawai::class, 'ppk_id');
    }

    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(Pegawai::class, 'penanggung_jawab_id');
    }

    public function petugasVerifikasi()
    {
        return $this->belongsTo(Pegawai::class, 'petugas_verifikasi_id');
    }
}