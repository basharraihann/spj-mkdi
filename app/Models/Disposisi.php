<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disposisi extends Model
{
    use HasFactory;

    protected $table = 'disposisi';

    /**
     * Daftar petunjuk standar lembar disposisi (urutan sesuai formulir cetak).
     * Index 0-7 = kolom kiri, 8 dst = kolom kanan.
     */
    public const PETUNJUK = [
        'Edarkan',
        'Bicarakan dengan saya',
        'Setuju',
        'Ditolak',
        'Perbaiki',
        'Koordinasikan',
        'Tindak lanjuti sesuai ketentuan',
        'Harap saran / masukan',
        'Siapkan jawaban sesuai ketentuan',
        'Siapkan bahan',
        'Untuk dibicarakan dengan saya',
        'Untuk diselesaikan',
        'Untuk dipelajari',
        'Untuk diketahui',
        'Untuk diarsipkan',
        'Harap mewakili',
        'Hadir bersama saya',
    ];

    protected $fillable = [
        'surat_masuk_id',
        'parent_dispo_id',
        'pengirim_id',
        'penerima_id',
        'instruksi',
        'petunjuk',
        'tenggat_waktu',
        'sifat',
        'perlu_st',
        'is_tembusan',
        'status',
        'dibaca_at',
    ];

    protected $casts = [
        'petunjuk' => 'array',
        'tenggat_waktu' => 'date',
        'dibaca_at' => 'datetime',
        'perlu_st' => 'boolean',
        'is_tembusan' => 'boolean',
    ];

    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'penerima_id');
    }

    public function parent()
    {
        return $this->belongsTo(Disposisi::class, 'parent_dispo_id');
    }

    public function children()
    {
        return $this->hasMany(Disposisi::class, 'parent_dispo_id');
    }

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'disposisi_id');
    }

    public function suratTugas()
    {
        return $this->hasMany(SuratTugas::class, 'disposisi_id');
    }

    public function notaDinas()
    {
        return $this->hasOne(NotaDinas::class, 'disposisi_id');
    }
}