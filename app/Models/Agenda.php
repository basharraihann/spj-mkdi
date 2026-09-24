<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $guarded = [];

    /**
     * Daftar kategori dokumen pendukung yang wajib diupload per agenda.
     * Dipakai bersama oleh AgendaController (index & dokumenForm) dan view.
     */
    public const KATEGORI_DOKUMEN = [
        'undangan' => 'Undangan',
        'surat_tugas' => 'Surat Tugas',
        'sppd_lanjutan' => 'SPPD Lanjutan',
        'laporan_kegiatan' => 'Laporan Kegiatan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        // Komponen biaya & jenis uang harian yang dipilih utk agenda ini (step "Buat Agenda")
        'komponen_biaya' => 'array',
        'jenis_uang_harian' => 'array',
    ];

    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class, 'agenda_pegawai')
            ->withPivot([
                'tiket',
                'dukungan_transportasi',
                'transportasi_darat',
                'transportasi_lokal',
                'peng_riil',
                'peng_riil_mode',
                'peng_riil_rate_id',
                // Detail multi-entry Peng. Riil (JSON array), sumber kebenaran tiap
                // baris entry — lihat migration add_peng_riil_detail dan
                // AgendaController@pesertaStore untuk cara ngisi/parsing-nya.
                'peng_riil_detail',
                'hotel',
                'penginapan_30',
                'lumpsum',
                'hari_dinas_biasa',
                'rate_dinas_biasa',
                'hari_biasa_60',
                'rate_biasa_60',
                'hari_fullday',
                'rate_fullday',
                'hari_fullboard',
                'rate_fullboard',
                'representatif',
                'belanja_bahan',
                'honor_narsum',
            ])->withTimestamps();
    }

    public function dokumenUploads()
    {
        return $this->hasMany(DokumenUpload::class);
    }

    public function dokumen(string $kategori)
    {
        return $this->dokumenUploads->where('kategori', $kategori)->first();
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

    /**
     * PIC (Person In Charge) yang membuat/mengurus agenda ini — beda dari
     * penanggungJawab() (Penanggung Jawab Kegiatan) yang muncul di SPD/nominatif.
     */
    public function pic()
    {
        return $this->belongsTo(Pegawai::class, 'pic_id');
    }

    public function getTotalBiayaAttribute()
    {
        return $this->pegawai->sum(fn($p) => $this->hitungBiaya($p));
    }

    protected function hitungBiaya($p)
    {
        // Lumpsum = jumlah dari semua pasang hari x rate yang aktif (4 jenis UH)
        $lumpsum = ($p->pivot->hari_dinas_biasa ?? 0) * ($p->pivot->rate_dinas_biasa ?? 0)
            + ($p->pivot->hari_biasa_60 ?? 0) * ($p->pivot->rate_biasa_60 ?? 0)
            + ($p->pivot->hari_fullday ?? 0) * ($p->pivot->rate_fullday ?? 0)
            + ($p->pivot->hari_fullboard ?? 0) * ($p->pivot->rate_fullboard ?? 0);

        return ($p->pivot->tiket ?? 0)
            + ($p->pivot->dukungan_transportasi ?? 0)
            + ($p->pivot->transportasi_darat ?? 0)
            + ($p->pivot->transportasi_lokal ?? 0)
            + ($p->pivot->peng_riil ?? 0)
            + ($p->pivot->hotel ?? 0)
            + ($p->pivot->penginapan_30 ?? 0)
            + $lumpsum
            + ($p->pivot->representatif ?? 0)
            + ($p->pivot->belanja_bahan ?? 0)
            + ($p->pivot->honor_narsum ?? 0);
    }

    public function getBiayaAsnAttribute()
    {
        return $this->pegawai
            ->where('status_kepegawaian', 'PNS')
            ->sum(fn($p) => $this->hitungBiaya($p));
    }

    public function getBiayaNonAsnAttribute()
    {
        return $this->pegawai
            ->where('status_kepegawaian', 'Non PNS')
            ->sum(fn($p) => $this->hitungBiaya($p));
    }

    public function rincianTransfer(string $statusLabel)
    {
        return $this->pegawai
            ->where('status_kepegawaian', $statusLabel)
            ->map(fn($p) => [
                'nama' => $p->nama,
                'keterangan' => 'Perjalanan Dinas ke ' . $this->tujuan,
                'total_bersih' => $this->hitungBiaya($p),
            ])
            ->values();
    }
}