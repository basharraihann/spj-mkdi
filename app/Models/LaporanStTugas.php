<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanStTugas extends Model
{
    protected $table = 'laporan_st_tugas';

    protected $fillable = ['surat_tugas_id', 'user_id', 'status'];

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_SELESAI = 'selesai';   // akun ini yang mengirim laporan
    public const STATUS_DITUTUP = 'ditutup';   // peserta lain sudah mengirim

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
