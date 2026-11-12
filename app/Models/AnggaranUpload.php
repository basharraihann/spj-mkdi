<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class AnggaranUpload extends Model
{
    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        // Upload dihapus (mis. lewat aturan KEEP_UPLOADS) => file aslinya ikut dihapus.
        static::deleting(function (self $u) {
            if ($u->file_path) {
                Storage::disk('local')->delete($u->file_path);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(AnggaranItem::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}