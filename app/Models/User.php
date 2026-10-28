<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/*
 * `pegawai_id` sengaja TIDAK masuk Fillable: pemetaan akun ke pegawai hanya diubah
 * lewat UserPegawaiController (khusus admin), bukan lewat form user biasa.
 */
#[Fillable(['name', 'username', 'email', 'password', 'role', 'unit_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Role Constants (5 role standar)
     */
    public const ROLE_TU = 'tu';
    public const ROLE_ESELON1 = 'eselon1';
    public const ROLE_ESELON2 = 'eselon2';
    public const ROLE_ESELON3 = 'eselon3';
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * Daftar role untuk dropdown form user dan aturan validasi.
     */
    public const ROLE_LABEL = [
        self::ROLE_TU => 'Staf Persuratan / TU',
        self::ROLE_ESELON1 => 'Eselon I (Deputi)',
        self::ROLE_ESELON2 => 'Eselon II (Asdep / Sesdep / Kabiro)',
        self::ROLE_ESELON3 => 'Eselon III / Madya (Pemeriksa Konsep)',
        self::ROLE_USER => 'Pegawai Pelaksana / Staf',
        self::ROLE_ADMIN => 'Administrator Sistem',
    ];

    /**
     * Unit tempat user bertugas
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Data pegawai yang dipetakan ke akun ini (null = belum dipetakan)
     */
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    /** Check if user is Admin */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Check if user is Staf Persuratan / TU */
    public function isTu(): bool
    {
        return $this->role === self::ROLE_TU;
    }

    /** Check if user is Eselon I (Deputi) */
    public function isEselon1(): bool
    {
        return $this->role === self::ROLE_ESELON1;
    }

    /** Check if user is Eselon II (Asdep / Sesdep / Kabiro) */
    public function isEselon2(): bool
    {
        return $this->role === self::ROLE_ESELON2;
    }

    /** Check if user is Eselon III / Madya (pemeriksa konsep Surat Keluar) */
    public function isEselon3(): bool
    {
        return $this->role === self::ROLE_ESELON3;
    }

    /** Check if user is Regular User / Staf Pelaksana */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Setelah login, TU / Eselon 1 / Eselon 2 yang terhubung ke sebuah unit langsung ke Surat Masuk.
     * Role lama (deputi-d2, asdep-d2, tu-d2) tetap didukung.
     */
    public function landsOnSuratMasuk(): bool
    {
        if (in_array($this->role, ['deputi-d2', 'asdep-d2', 'tu-d2'], true)) {
            return true;
        }

        return $this->unit_id !== null
            && in_array($this->role, [self::ROLE_TU, self::ROLE_ESELON1, self::ROLE_ESELON2], true);
    }

    /** Pimpinan = Eselon 1 & Eselon 2 */
    public function isPimpinan(): bool
    {
        return in_array($this->role, [
            self::ROLE_ESELON1,
            self::ROLE_ESELON2,
        ], true);
    }

    /**
     * Boleh membuka halaman monitoring (Eselon 1, Eselon 2, TU, atau Admin).
     */
    public function bolehMonitoring(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_TU,
            self::ROLE_ESELON1,
            self::ROLE_ESELON2,
            self::ROLE_ESELON3,
        ], true);
    }
    public function bolehMenu(string $key): bool
    {
        $akses = config('menu.akses.' . $this->role, []);

        return $akses === '*' || in_array($key, $akses, true);
    }
    /**
     * Label role
     */
    public function getRoleLabelAttribute(): string
    {
        return self::ROLE_LABEL[$this->role] ?? ucfirst($this->role ?? 'User');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}