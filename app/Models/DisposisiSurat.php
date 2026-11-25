<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Surat masuk yang didisposisikan. Dicatat oleh TU (TU Unit / TU Sekretariat) atau admin.
 */
class DisposisiSurat extends Model
{
    protected $table = 'disposisi_surat';

    public const SIFAT = [
        'biasa' => 'Biasa',
        'segera' => 'Segera',
        'sangat_segera' => 'Sangat Segera',
        'rahasia' => 'Rahasia',
    ];

    protected $fillable = [
        'unit_id', 'nomor_agenda', 'nomor_surat', 'tanggal_surat', 'tanggal_terima', 'asal_surat',
        'perihal', 'sifat', 'lampiran', 'file_path', 'file_name', 'status', 'selesai_at', 'dibuat_oleh',
        'tanggal_kegiatan', 'waktu_mulai', 'waktu_selesai', 'tempat',
    ];

    /** Role yang boleh memakai menu Disposisi (input & semua disposisi). */
    public const ROLE_PENGELOLA = [User::ROLE_TU_UNIT, User::ROLE_ESELON2, User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN];

    public static function bolehKelola(?User $user): bool
    {
        return $user && in_array($user->role, self::ROLE_PENGELOLA, true);
    }

    protected function casts(): array
    {
        return [
            'tanggal_surat' => 'date',
            'tanggal_terima' => 'date',
            'selesai_at' => 'datetime',
            'tanggal_kegiatan' => 'date',
        ];
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(DisposisiFeedback::class, 'surat_id')->orderBy('id');
    }

    /** "09:00 - 11:00" / "09:00" / null */
    public function getWaktuLabelAttribute(): ?string
    {
        $m = $this->waktu_mulai ? substr((string) $this->waktu_mulai, 0, 5) : null;
        $s = $this->waktu_selesai ? substr((string) $this->waktu_selesai, 0, 5) : null;

        return $m ? ($s ? "{$m} - {$s}" : $m) : null;
    }

    /**
     * Surat selesai kalau semua cabang disposisinya tuntas.
     * Tuntas = selesai, atau diteruskan dan semua anaknya tuntas.
     */
    public function perbaruiStatus(): void
    {
        $semua = $this->disposisi()->get(['id', 'parent_id', 'status']);
        $anak = $semua->groupBy(fn($d) => $d->parent_id ?? 0);

        $tuntas = function ($d) use (&$tuntas, $anak): bool {
            if ($d->status === 'selesai') {
                return true;
            }
            if ($d->status !== 'diteruskan') {
                return false;
            }
            $c = $anak[$d->id] ?? collect();

            return $c->isNotEmpty() && $c->every(fn($x) => $tuntas($x));
        };

        $akar = $anak[0] ?? collect();
        $selesai = $akar->isNotEmpty() && $akar->every(fn($d) => $tuntas($d));

        $this->update([
            'status' => $selesai ? 'selesai' : 'proses',
            'selesai_at' => $selesai ? ($this->selesai_at ?? now()) : null,
        ]);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pembuat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_id')->orderBy('id');
    }

    public function getSifatLabelAttribute(): string
    {
        return self::SIFAT[$this->sifat] ?? ucfirst((string) $this->sifat);
    }

    /**
     * Surat yang boleh dilihat user:
     * - admin / super admin: semua
     * - TU Unit & Eselon II: surat unitnya + surat yang ia ikut terlibat
     * - lainnya: surat yang ia buat, kirim, atau terima disposisinya
     */
    public function scopeTerlihatOleh($q, User $user)
    {
        if ($user->isAdmin()) {
            return $q;
        }

        return $q->where(function ($w) use ($user) {
            $w->where('dibuat_oleh', $user->id)
                ->orWhereHas('disposisi', fn($d) => $d->where(
                    fn($x) => $x->where('dari_user_id', $user->id)->orWhere('kepada_user_id', $user->id)
                ));

            if (in_array($user->role, [User::ROLE_TU_UNIT, User::ROLE_ESELON2], true) && $user->unit_id) {
                $w->orWhere('unit_id', $user->unit_id);
            }
        });
    }

    public function bolehDilihat(User $user): bool
    {
        return static::query()->whereKey($this->id)->terlihatOleh($user)->exists();
    }
}
