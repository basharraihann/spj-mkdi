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

    /**
     * Alur sesuai Diagram D2 Surat Keluar:
     * draft (buat/revisi konsep, TU) -> diajukan (Es 3 / Madya periksa konsep)
     * -> diperiksa_es3 (Es 2 & Admin persetujuan) -> disetujui_es2 (proses TTD/TTE)
     * -> [disetujui_es1, hanya bila perlu_es1] -> terbit (TU terima surat TTD & penomoran)
     * -> terkirim (TU kirim ke unit tujuan = selesai).
     * "Perlu Revisi" dari Es 3 / Es 2 mengembalikan dokumen ke draft.
     */
    public const STATUS_LABEL = [
        'draft' => 'Draf',
        'diajukan' => 'Diajukan / Diperiksa Eselon 3',
        'diperiksa_es3' => 'Diperiksa Eselon 3 (menunggu Eselon 2)',
        'disetujui_es2' => 'Disetujui Eselon 2 (proses TTD/TTE)',
        'disetujui_es1' => 'Disetujui Eselon 1',
        'terbit' => 'Terbit (TTD diterima & bernomor)',
        'terkirim' => 'Terkirim ke Unit Tujuan (Selesai)',
    ];

    /** Label pendek untuk badge tabel & kartu rekap agar tidak membungkus baris. */
    public const STATUS_SINGKAT = [
        'draft' => 'Draf',
        'diajukan' => 'Periksa Es 3',
        'diperiksa_es3' => 'Menunggu Es 2',
        'disetujui_es2' => 'Proses TTD/TTE',
        'disetujui_es1' => 'Disetujui Es 1',
        'terbit' => 'Terbit',
        'terkirim' => 'Terkirim',
    ];

    public function getStatusSingkatAttribute(): string
    {
        return self::STATUS_SINGKAT[$this->status_persetujuan] ?? $this->status_label;
    }

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

    /** Sudah ditandatangani & siap diterima TU untuk penomoran/penerbitan. */
    public function getSiapTerbitAttribute(): bool
    {
        return $this->status_persetujuan === 'disetujui_es1'
            || ($this->status_persetujuan === 'disetujui_es2' && !$this->butuh_eselon1);
    }

    /** Siapa yang boleh menekan "Perlu Revisi" (kembalikan ke TU) pada status saat ini. */
    public function bolehDikembalikanOleh(User $user): bool
    {
        if ($user->isAdmin()) {
            return in_array($this->status_persetujuan, ['diajukan', 'diperiksa_es3', 'disetujui_es2', 'disetujui_es1'], true);
        }

        return match (true) {
            $user->isEselon3() => $this->status_persetujuan === 'diajukan',
            $user->isEselon2() => in_array($this->status_persetujuan, ['diperiksa_es3', 'disetujui_es2'], true),
            $user->isEselon1() => in_array($this->status_persetujuan, ['disetujui_es2', 'disetujui_es1'], true),
            default => false,
        };
    }

    /** Hanya draf yang boleh diedit/dihapus (kecuali admin). */
    public function getBisaDiubahAttribute(): bool
    {
        return $this->status_persetujuan === 'draft';
    }
}
