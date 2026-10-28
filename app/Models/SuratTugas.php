<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SuratTugas extends Model
{
    protected $table = 'surat_tugas';

    /**
     * `tusi_id` sengaja TIDAK ada di sini: store() dan update() tidak boleh menyentuhnya.
     * Tusi hanya diubah lewat SuratTugasController@updateTusi.
     */
    protected $fillable = [
        'nomor',
        'nomor_urut',
        'tahun',
        'kode_klasifikasi',
        'status',
        'menimbang',
        'dasar',
        'untuk',
        'kepada',
        'tempat',
        'tanggal',
        'tanggal_mulai',
        'tanggal_selesai',
        'provinsi',
        'kota',
        'tujuan',
        'jabatan_penandatangan',
        'nama_penandatangan',
        'nip_penandatangan',
        'arsip_surat_id',
        'created_by',
        'unit_id',
        'diterbitkan_at',
        'dibatalkan_at',
        'alasan_batal',
        'file_ttd_path',
        'file_ttd_nama',
        'ditandatangani_by',
        'ditandatangani_at',
    ];

    protected $casts = [
        'dasar' => 'array',
        'untuk' => 'array',
        'tanggal' => 'date',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'diterbitkan_at' => 'datetime',
        'dibatalkan_at' => 'datetime',
        'ditandatangani_at' => 'datetime',
    ];

    public const STATUS_LABEL = [
        'draf' => 'Draf',
        'terbit' => 'Terbit',
        'selesai' => 'Selesai',
        'batal' => 'Batal',
    ];

    public function peserta(): HasMany
    {
        return $this->hasMany(SuratTugasPegawai::class)->orderBy('urutan');
    }

    /** Lembar kontrol: tahapan konseptor, persetujuan Eselon 2, kontrol Sesdep, persetujuan Sesdep. */
    public function kontrol(): HasMany
    {
        return $this->hasMany(SuratTugasKontrol::class)->orderBy('urutan');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function tusi(): BelongsTo
    {
        return $this->belongsTo(Tusi::class);
    }

    /** User yang mengunggah file TTD dan menerbitkan ST. */
    public function penandatanganiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditandatangani_by');
    }

    /** Laporan hasil ST (satu per ST; belum ada baris = belum diisi). */
    public function laporan(): HasOne
    {
        return $this->hasOne(LaporanSt::class);
    }

    /** Penugasan "Isi Laporan" per akun peserta. */
    public function tugasLapor(): HasMany
    {
        return $this->hasMany(LaporanStTugas::class);
    }

    public function isDraf(): bool
    {
        return $this->status === 'draf';
    }

    /**
     * Jalur persetujuan Sesdep: sudah punya lembar kontrol, atau unit pembuat berkode "... D<n>"
     * (Asdep 3 D2, Sekretariat D2, dst). ST Biro MKDI dan ST lama tanpa unit memakai jalur lama.
     * Aturan kode unit sama dengan KontrolStController@mulai.
     */
    public function memakaiAlurSesdep(): bool
    {
        $adaKontrol = $this->relationLoaded('kontrol')
            ? $this->kontrol->isNotEmpty()
            : $this->kontrol()->exists();

        if ($adaKontrol) {
            return true;
        }

        return $this->unit_id
            && preg_match('/\sD\d$/', (string) optional($this->unit)->kode) === 1;
    }

    /**
     * ST sedang/selesai diproses lembar kontrol, jadi isinya dikunci.
     * Terbuka lagi hanya kalau ada tahap yang ditolak (butuh revisi).
     */
    public function terkunciKontrol(): bool
    {
        return $this->kontrol()->exists()
            && !$this->kontrol()->where('status', 'ditolak')->exists();
    }

    /**
     * Batasi ST yang boleh dilihat user:
     * admin = semua; selain itu pembuat, petugas lembar kontrol, peserta (lewat pemetaan pegawai),
     * dan satu unit dengan pembuat. ST lama tanpa unit tetap terlihat oleh TU dan pimpinan.
     */
    public function scopeDapatDilihat(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(function (Builder $w) use ($user) {
            $w->where('created_by', $user->id)
                ->orWhereHas('kontrol', fn($k) => $k->where('petugas_id', $user->id));

            if ($user->pegawai_id) {
                $w->orWhereHas('peserta', fn($p) => $p->where('pegawai_id', $user->pegawai_id));
            }
            if ($user->unit_id) {
                $w->orWhere('unit_id', $user->unit_id);
            }
            if ($user->isTu() || $user->isPimpinan()) {
                $w->orWhereNull('unit_id');
            }
        });
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABEL[$this->status] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draf' => 'bg-gray-100 text-gray-700',
            'terbit' => 'bg-blue-100 text-blue-700',
            'selesai' => 'bg-green-100 text-green-700',
            'batal' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function getTanggalIndoAttribute(): string
    {
        return $this->tanggal->copy()->locale('id')->translatedFormat('j F Y');
    }

    /** Nomor untuk ditampilkan (draf: placeholder). */
    public function getNomorTampilAttribute(): string
    {
        return $this->nomor ?: '.../' . $this->kode_klasifikasi . '/' . $this->tanggal->year;
    }

    /** "7 – 10 September 2026", atau "7 September 2026" kalau sehari. Null kalau belum diisi. */
    public function getRentangTanggalAttribute(): ?string
    {
        if (!$this->tanggal_mulai) {
            return null;
        }

        $a = $this->tanggal_mulai->copy()->locale('id');
        $b = ($this->tanggal_selesai ?? $this->tanggal_mulai)->copy()->locale('id');

        if ($a->isSameDay($b)) {
            return $a->translatedFormat('j F Y');
        }
        if ($a->isSameMonth($b)) {
            return $a->translatedFormat('j') . ' – ' . $b->translatedFormat('j F Y');
        }

        return $a->translatedFormat('j F Y') . ' – ' . $b->translatedFormat('j F Y');
    }

    /** "Kota Denpasar, Bali" atau null kalau belum diisi. */
    public function getLokasiAttribute(): ?string
    {
        return collect([$this->kota, $this->provinsi])->filter()->implode(', ') ?: null;
    }
}