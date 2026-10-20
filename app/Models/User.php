<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
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
     * Role Constants
     */
    public const ROLE_ADMIN = 'admin';
    public const ROLE_PIMPINAN = 'pimpinan';
    public const ROLE_USER = 'user';

    /**
     * Daftar role untuk dropdown form user dan aturan validasi.
     * Satu sumber: tambah role baru cukup di sini.
     */
    public const ROLE_LABEL = [
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_PIMPINAN => 'Pimpinan',
        self::ROLE_USER => 'Staf / User',
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

    /**
     * Check if user is Admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is Pimpinan
     */
    public function isPimpinan(): bool
    {
        return $this->role === self::ROLE_PIMPINAN;
    }

    /**
     * Check if user is Regular User / Staf
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Boleh membuka halaman monitoring (pimpinan, atau admin).
     */
    public function bolehMonitoring(): bool
    {
        return $this->isAdmin() || $this->isPimpinan();
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