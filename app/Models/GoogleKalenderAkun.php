<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Token Google Calendar milik satu user (token disimpan terenkripsi). */
class GoogleKalenderAkun extends Model
{
    protected $table = 'google_kalender_akun';

    protected $fillable = [
        'user_id', 'google_email', 'access_token', 'refresh_token', 'expires_at', 'calendar_id',
        'terakhir_sinkron', 'error_terakhir',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'expires_at' => 'datetime',
            'terakhir_sinkron' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
