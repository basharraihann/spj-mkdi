<?php

namespace App\Models\Concerns;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToUnit
{
    protected static function bootBelongsToUnit(): void
    {
        // Non-admin hanya lihat data unit sendiri
        static::addGlobalScope('unit', function (Builder $builder) {
            $user = auth()->user();

            if ($user && !$user->isAdmin()) {
                $builder->where(
                    $builder->getModel()->getTable() . '.unit_id',
                    $user->unit_id
                );
            }
        });

        // Non-admin: unit_id otomatis diisi dari akunnya
        static::creating(function ($model) {
            $user = auth()->user();

            if ($user && !$user->isAdmin()) {
                $model->unit_id = $user->unit_id;
            }
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}