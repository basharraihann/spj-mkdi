<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaDinas extends Model
{
    use HasFactory;

    protected $table = 'nota_dinas';

    protected $fillable = [
        'disposisi_id',
        'nomor_nd',
        'perihal',
        'ringkasan_hasil',
        'drive_link_hasil',
        'pembuat_id',
    ];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class, 'disposisi_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'pembuat_id');
    }
}