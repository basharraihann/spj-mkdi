<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Feedback / tanggapan atas disposisi (dari penerima maupun pengirim). */
class DisposisiFeedback extends Model
{
    protected $table = 'disposisi_feedback';

    protected $fillable = ['surat_id', 'disposisi_id', 'user_id', 'isi'];

    public function surat(): BelongsTo
    {
        return $this->belongsTo(DisposisiSurat::class, 'surat_id');
    }

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
