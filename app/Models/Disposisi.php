<?php

namespace App\Models;

use App\Services\GoogleKalenderService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Satu langkah disposisi: dari satu user kepada satu user.
 * Diteruskan = membuat disposisi anak (parent_id = disposisi ini).
 */
class Disposisi extends Model
{
    protected $table = 'disposisi';

    /** Pilihan instruksi (bisa dicentang lebih dari satu). */
    public const INSTRUKSI = [
        'tindak_lanjuti' => 'Tindak lanjuti',
        'pelajari' => 'Pelajari / telaah',
        'siapkan_bahan' => 'Siapkan bahan / jawaban',
        'hadiri' => 'Hadiri / wakili',
        'koordinasikan' => 'Koordinasikan',
        'untuk_diketahui' => 'Untuk diketahui',
        'edarkan' => 'Edarkan',
        'arsipkan' => 'Arsipkan',
    ];

    public const STATUS = [
        'baru' => 'Belum dibaca',
        'dibaca' => 'Sudah dibaca',
        'diteruskan' => 'Diteruskan',
        'selesai' => 'Selesai',
    ];

    /** Status yang masih perlu tindakan penerima. */
    public const TERBUKA = ['baru', 'dibaca'];

    protected $fillable = [
        'surat_id',
        'parent_id',
        'dari_user_id',
        'kepada_user_id',
        'instruksi',
        'catatan',
        'batas_waktu',
        'status',
        'dibaca_at',
        'diteruskan_at',
        'selesai_at',
        'laporan',
        'laporan_file_path',
        'laporan_file_name',
        'google_event_id',
    ];

    protected static function booted(): void
    {
        // Disposisi diteruskan -> event di Google Calendar ditandai "→ diteruskan".
        // Dijalankan setelah transaksi commit supaya panggilan HTTP tidak menahan database.
        static::updated(function (Disposisi $d) {
            if ($d->wasChanged('status') && $d->status === 'diteruskan' && $d->google_event_id) {
                DB::afterCommit(fn() => GoogleKalenderService::sinkron($d->fresh()));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'instruksi' => 'array',
            'batas_waktu' => 'date',
            'dibaca_at' => 'datetime',
            'diteruskan_at' => 'datetime',
            'selesai_at' => 'datetime',
        ];
    }

    public function surat(): BelongsTo
    {
        return $this->belongsTo(DisposisiSurat::class, 'surat_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function anak(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id');
    }

    public function dari(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function kepada(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kepada_user_id');
    }

    /**
     * Tanggal disposisi di kalender:
     * tanggal kegiatan surat -> batas waktu -> tanggal disposisi dikirim.
     */
    public function getTanggalKalenderAttribute()
    {
        return $this->surat?->tanggal_kegiatan ?? $this->batas_waktu ?? $this->created_at->copy()->startOfDay();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /**
     * Permohonan disposisi = disposisi pertama dari TU ke Eselon II (belum berisi instruksi).
     * Yang sudah berisi instruksi adalah penunjukan disposisi (Eselon II ke bawahan).
     */
    public function getPermohonanAttribute(): bool
    {
        return empty($this->instruksi);
    }

    /** Label instruksi yang dicentang. */
    public function getInstruksiLabelAttribute(): array
    {
        $label = collect($this->instruksi ?? [])
            ->map(fn($k) => self::INSTRUKSI[$k] ?? $k)->values()->all();

        // Disposisi pertama dari TU belum berisi instruksi
        if (!$label && $this->parent_id === null) {
            return ['Permohonan disposisi'];
        }

        return $label;
    }

    public function getTerlambatAttribute(): bool
    {
        return $this->batas_waktu
            && in_array($this->status, self::TERBUKA, true)
            && $this->batas_waktu->lt(now()->startOfDay());
    }

    /** Jumlah disposisi yang belum dibaca user (untuk badge menu). */
    public static function jumlahBaru(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }

        // Dibungkus try: kalau migrasi disposisi belum dijalankan, menu tetap tampil (badge 0)
        // dan halaman lain tidak ikut error.
        try {
            return static::where('kepada_user_id', $userId)->where('status', 'baru')->count();
        } catch (\Illuminate\Database\QueryException) {
            return 0;
        }
    }
}