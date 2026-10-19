<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanStTindakLanjut extends Model
{
    protected $table = 'laporan_st_tindak_lanjut';

    protected $fillable = ['laporan_st_id', 'urutan', 'uraian', 'pic', 'tenggat'];

    protected $casts = ['tenggat' => 'date'];

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanSt::class, 'laporan_st_id');
    }
}
