<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    // Nama tabel di migration singular ('pegawai'), beda dari default Eloquent ('pegawais')
    protected $table = 'pegawai';

    protected $guarded = [];

    public const STATUS_KEPEGAWAIAN = [
        'PNS' => 'PNS (ASN)',
        'Non PNS' => 'Non PNS',
    ];

    public const ROLE_PENANDATANGAN = [
        'PPK' => 'PPK',
        'Bendahara' => 'Bendahara',
        'Penanggung Jawab Kegiatan' => 'Penanggung Jawab Kegiatan',
        'Petugas Verifikasi' => 'Petugas Verifikasi',
    ];

    /**
     * Urutan golongan PNS dari terendah ke tertinggi, dipakai untuk sorting.
     */
    public const GOLONGAN_URUTAN = [
        'I/a',
        'I/b',
        'I/c',
        'I/d',
        'II/a',
        'II/b',
        'II/c',
        'II/d',
        'III/a',
        'III/b',
        'III/c',
        'III/d',
        'IV/a',
        'IV/b',
        'IV/c',
        'IV/d',
        'IV/e',
    ];

    /**
     * Ranking numerik golongan (makin besar = makin tinggi). -1 kalau golongan
     * kosong/tidak dikenali, supaya otomatis ke bawah saat diurutkan descending.
     */
    public function getGolonganRankAttribute(): int
    {
        $index = array_search($this->golongan, self::GOLONGAN_URUTAN, true);

        return $index === false ? -1 : $index;
    }

    public function agendas()
    {
        return $this->belongsToMany(Agenda::class, 'agenda_pegawai')
            ->withPivot(['tiket', 'peng_riil', 'lumpsum', 'representatif', 'hotel'])
            ->withTimestamps();
    }
}