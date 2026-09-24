<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Nomor memo yang dibuat mandiri (tanpa lewat form agenda), lewat halaman
 * "Buat Nomor Memo". Lihat App\Services\NomorMemoService untuk cara ini
 * digabung dengan nomor_memo_pns/non_pns di tabel agendas.
 */
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
}
