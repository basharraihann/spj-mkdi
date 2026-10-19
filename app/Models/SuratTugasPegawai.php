<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratTugasPegawai extends Model
{
    protected $table = 'surat_tugas_pegawai';

    // 'jenis' sebelumnya belum ada di $fillable, jadi PNS / Non PNS tidak pernah tersimpan.
    protected $fillable = [
        'surat_tugas_id', 'pegawai_id', 'urutan', 'jenis',
        'nama', 'nip', 'jabatan', 'tanggal_mulai', 'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function suratTugas(): BelongsTo
    {
        return $this->belongsTo(SuratTugas::class);
    }

    public function getJenisLabelAttribute(): string
    {
        return $this->jenis === 'non_pns' ? 'Non PNS' : 'PNS';
    }

    /** "14 Oktober 2026", "14 s.d. 15 Oktober 2026", atau null bila tidak diisi (ikut rentang ST). */
    public function getTanggalTugasIndoAttribute(): ?string
    {
        if (! $this->tanggal_mulai) {
            return null;
        }

        $a = $this->tanggal_mulai->copy()->locale('id');
        $b = $this->tanggal_selesai?->copy()->locale('id');

        if (! $b || $a->isSameDay($b)) {
            return $a->translatedFormat('j F Y');
        }
        if ($a->format('Y-m') === $b->format('Y-m')) {
            return $a->translatedFormat('j').' s.d. '.$b->translatedFormat('j F Y');
        }

        return $a->translatedFormat('j F Y').' s.d. '.$b->translatedFormat('j F Y');
    }
}