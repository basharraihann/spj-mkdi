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
     * Role Constants (Sesuai 5 Role Baku Sistem Disposisi)
     */
    public const ROLE_TU = 'tu';
    public const ROLE_ESELON1 = 'eselon1';
    public const ROLE_ESELON2 = 'eselon2';
    public const ROLE_USER = 'user';
    public const ROLE_ADMIN = 'admin';

    /**
     * Daftar role untuk dropdown form user dan aturan validasi.
     */
    public const ROLE_LABEL = [
        self::ROLE_TU => 'Staf Persuratan / TU',
        self::ROLE_ESELON1 => 'Eselon I (Deputi)',
        self::ROLE_ESELON2 => 'Eselon II (Asdep / Sesdep / Kabiro)',
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

    /** Check if user is Regular User / Staf Pelaksana */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
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