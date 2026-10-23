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
     * Role Constants (Sesuai Role Standar & Role Khusus Deputi 2)
     */
    public const ROLE_TU = 'tu';
    public const ROLE_ESELON1 = 'eselon1';
    public const ROLE_ESELON2 = 'eselon2';
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PIMPINAN = 'pimpinan';

    // Role Khusus Deputi 2
    public const ROLE_TU_D2 = 'tu-d2';
    public const ROLE_DEPUTI_D2 = 'deputi-d2';
    public const ROLE_ASDEP_D2 = 'asdep-d2';
    public const ROLE_PEGAWAI_D2 = 'pegawai-d2';
    public const ROLE_ADMIN_D2 = 'admin-d2';

    /**
     * Daftar role untuk dropdown form user dan aturan validasi.
     */
    public const ROLE_LABEL = [
        self::ROLE_TU => 'Staf Persuratan / TU',
        self::ROLE_ESELON1 => 'Eselon I (Deputi)',
        self::ROLE_ESELON2 => 'Eselon II (Asdep / Sesdep / Kabiro)',
        self::ROLE_USER => 'Pegawai Pelaksana / Staf',
        self::ROLE_ADMIN => 'Administrator Sistem',
        self::ROLE_PIMPINAN => 'Pimpinan',

        self::ROLE_TU_D2 => 'TU Deputi 2',
        self::ROLE_DEPUTI_D2 => 'Ibu Deputi 2 (Eselon I)',
        self::ROLE_ASDEP_D2 => 'Bapak Asdep Deputi 2 (Eselon II)',
        self::ROLE_PEGAWAI_D2 => 'Pegawai Pelaksana / Staf D2',
        self::ROLE_ADMIN_D2 => 'Administrator Deputi 2',
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
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_ADMIN_D2], true);
    }

    /** Check if user is Staf Persuratan / TU */
    public function isTu(): bool
    {
        return in_array($this->role, [self::ROLE_TU, self::ROLE_TU_D2], true);
    }

    /** Check if user is Eselon I (Deputi) */
    public function isEselon1(): bool
    {
        return in_array($this->role, [self::ROLE_ESELON1, 'deputi', self::ROLE_DEPUTI_D2], true);
    }

    /** Check if user is Eselon II (Asdep / Sesdep / Kabiro) */
    public function isEselon2(): bool
    {
        return in_array($this->role, [self::ROLE_ESELON2, 'asdep', self::ROLE_ASDEP_D2], true);
    }

    /** Check if user is Regular User / Staf Pelaksana */
    public function isUser(): bool
    {
        return in_array($this->role, [self::ROLE_USER, 'pegawai', self::ROLE_PEGAWAI_D2], true);
    }

    /** Check if user is Pimpinan (Eselon 1, Eselon 2, Deputi, Asdep) */
    public function isPimpinan(): bool
    {
        return in_array($this->role, [
            self::ROLE_PIMPINAN,
            self::ROLE_ESELON1,
            self::ROLE_ESELON2,
            self::ROLE_DEPUTI_D2,
            self::ROLE_ASDEP_D2,
            'deputi',
            'asdep'
        ], true);
    }

    /**
     * Boleh membuka halaman monitoring (Eselon 1, Eselon 2, TU, atau Admin).
     */
    public function bolehMonitoring(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_ADMIN_D2,
            self::ROLE_TU,
            self::ROLE_TU_D2,
            self::ROLE_ESELON1,
            self::ROLE_DEPUTI_D2,
            self::ROLE_ESELON2,
            self::ROLE_ASDEP_D2,
        ], true);
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