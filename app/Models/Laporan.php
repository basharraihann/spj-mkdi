<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laporan extends Model
{
    protected $table = 'laporan';

    protected $fillable = [
        'disposisi_id',
        'pelapor_id',
        'isi',
        'drive_link',
    ];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, 'disposisi_id');
    }

    public function pelapor()
    {
        return $this->belongsTo(User::class, 'pelapor_id');
    }
}
