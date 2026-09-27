<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Scope otomatis semua query ke sekolah (tenant) pengguna yang sedang login,
 * dan mengisi school_id otomatis saat membuat data baru.
 */
trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $schoolId = auth()->user()?->school_id;
            if ($schoolId) {
                $builder->where($builder->getModel()->getTable() . '.school_id', $schoolId);
            }
        });

        static::creating(function ($model) {
            if (empty($model->school_id) && auth()->user()) {
                $model->school_id = auth()->user()->school_id;
            }
        });
    }
}
