<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuratKeluar extends Model
{
    use HasFactory;

    protected $table = 'surat_keluars';

    protected $fillable = [
        'jenis_dokumen',
        'nomor_surat',
        'sifat',
        'hal',
        'tanggal_surat',
        'tujuan',
        'dari',
        'isi_payload',
        'tembusan',
        'created_by',
        'status_persetujuan',
        'file_scan_ttd',
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'isi_payload'    => 'array',
        'tembusan'        => 'array',
    ];

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNamaJenisAttribute(): string
    {
        return match ($this->jenis_dokumen) {
            'nota_dinas' => 'Nota Dinas',
            'undangan' => 'Undangan / Surat Dinas',
            'telaah_staf' => 'Telaah Staf',
            'notula' => 'Notula Rapat',
            'surat_pernyataan' => 'Surat Pernyataan',
            'laporan' => 'Laporan',
            default => 'Dokumen',
        };
    }

    // =========================================================
    // TAMBAHAN: pilihan template, status persetujuan, helper payload
    // =========================================================

    public const JENIS = [
        'nota_dinas' => 'Nota Dinas (TND / ND)',
        'undangan' => 'Undangan / Surat Dinas',
        'telaah_staf' => 'Telaah Staf',
        'notula' => 'Notula Rapat',
        'surat_pernyataan' => 'Surat Pernyataan',
        'laporan' => 'Laporan',
    ];

    /** Varian template per jenis dokumen (file template resmi 2026). */
    public const TEMPLATE = [
        'nota_dinas' => [
            'es2' => 'ND Eselon 2 (ke Deputi)',
            'es1_deputi2' => 'ND Eselon I Deputi 2 (ke Sekretaris Kemenko)',
        ],
        'undangan' => [
            'internal' => 'Undangan Internal (Es 1 & Es 2)',
            'eksternal' => 'Undangan Eksternal (berlogo, Es 1 & Es 2)',
            'surat_dinas' => 'Surat Dinas format undangan (Es 1 & Es 2)',
        ],
    ];

    public const STATUS_LABEL = [
        'draft' => 'Draf',
        'diajukan' => 'Diajukan / Diperiksa',
        'disetujui_es2' => 'Disetujui Eselon 2',
        'disetujui_es1' => 'Disetujui Eselon 1',
        'terbit' => 'Terbit (TTD Basah)',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status_persetujuan] ?? ucfirst((string) $this->status_persetujuan);
    }

    /** Ambil satu nilai dari isi_payload. */
    public function payload(string $key, $default = null)
    {
        return data_get($this->isi_payload, $key, $default);
    }

    /** Pecah isian textarea (satu butir per baris) menjadi array bersih. */
    public function payloadList(string $key): array
    {
        $v = $this->payload($key);
        if (is_array($v)) {
            return array_values(array_filter(array_map('trim', $v), 'strlen'));
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $v)), 'strlen'));
    }

    public function getDenganLampiranAttribute(): bool
    {
        return (bool) $this->payload('dengan_lampiran', false);
    }

    /** Undangan dari Deputi punya 2 tahap pemeriksaan: Eselon 2 lalu Eselon 1. */
    public function getButuhEselon1Attribute(): bool
    {
        return (bool) $this->payload('perlu_es1', false);
    }

    /** Hanya draf yang boleh diedit/dihapus (kecuali admin). */
    public function getBisaDiubahAttribute(): bool
    {
        return $this->status_persetujuan === 'draft';
    }
}
