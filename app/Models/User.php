<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ===== Role =====
    public const ROLE_USER = 'user';
    public const ROLE_TU_UNIT = 'tu_unit';
    public const ROLE_TU_SEKRETARIAT = 'tu_sekretariat';
    public const ROLE_ESELON2 = 'eselon2';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPER_ADMIN = 'super_admin';

    /**
     * Role lama sebelum TU dipecah jadi TU Unit dan TU Sekretariat.
     * Tidak bisa dipilih lagi di form. Hapus konstanta ini kalau sudah tidak dipakai di mana pun.
     */
    public const ROLE_TU = 'tu';

    /**
     * Label tampilan tiap role.
     */
    public const ROLE_LABELS = [
        self::ROLE_USER => 'Staf / User',
        self::ROLE_TU_UNIT => 'TU Unit',
        self::ROLE_TU_SEKRETARIAT => 'TU Sekretariat',
        self::ROLE_ESELON2 => 'Eselon II',
        self::ROLE_ADMIN => 'Administrator',
        self::ROLE_SUPER_ADMIN => 'Super Admin',
    ];

    public const AVATAR_STYLES = [
        'initials' => 'Inisial',
        'thumbs' => 'Jempol',
        'bottts' => 'Robot',
        'avataaars' => 'Kartun',
        'lorelei' => 'Lorelei',
        'notionists' => 'Notion',
        'adventurer' => 'Petualang',
        'fun-emoji' => 'Emoji',
        'shapes' => 'Bentuk',
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'role',
        'unit_id',
        'password',
        'avatar_style',
        'avatar_seed',
        'status_kepegawaian',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * URL avatar dari DiceBear. Seed pakai username supaya tidak berubah kalau nama diganti.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function () {
            $style = array_key_exists($this->avatar_style, self::AVATAR_STYLES)
                ? $this->avatar_style
                : 'initials';

            $base = $this->username ?: $this->name;
            $seed = $style === 'initials' ? $base : ($this->avatar_seed ?: $base);

            $params = ['seed' => $seed, 'backgroundColor' => 'e0e7ff', 'radius' => 50];
            if ($style === 'initials') {
                $params['textColor'] = '4f46e5';
            }

            return "https://api.dicebear.com/9.x/{$style}/svg?" . http_build_query($params);
        });
    }

    // ===== Relasi =====

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    // ===== Helper role =====

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Admin dan Super Admin diperlakukan setara.
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN], true);
    }

    /**
     * TU Unit atau TU Sekretariat.
     */
    public function isTu(): bool
    {
        return in_array($this->role, [self::ROLE_TU_UNIT, self::ROLE_TU_SEKRETARIAT], true);
    }

    public function isTuSekretariat(): bool
    {
        return $this->role === self::ROLE_TU_SEKRETARIAT;
    }

    public function isEselon2(): bool
    {
        return $this->role === self::ROLE_ESELON2;
    }

    /**
     * Boleh approval di Monitoring Memo.
     */
    public function canApproveMemo(): bool
    {
        return in_array($this->role, [
            self::ROLE_TU_SEKRETARIAT,
            self::ROLE_ADMIN,
            self::ROLE_SUPER_ADMIN,
        ], true);
    }

    /**
     * Nama role untuk ditampilkan, contoh: "TU Sekretariat".
     */
    public function roleLabel(): string
    {
        return self::ROLE_LABELS[$this->role] ?? ucfirst((string) $this->role);
    }
}