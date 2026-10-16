<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTugasPegawai extends Model
{
    protected $table = 'surat_tugas_pegawai';
    protected $fillable = ['surat_tugas_id', 'pegawai_id', 'urutan', 'nama', 'nip', 'jabatan'];

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class);
    }
}
