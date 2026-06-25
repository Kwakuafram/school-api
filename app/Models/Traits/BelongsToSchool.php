<?php

namespace App\Models\Traits;

use App\Models\School;
use App\Services\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToSchool
{
    protected static function bootBelongsToSchool(): void
    {
        static::creating(function ($model) {
            if (empty($model->school_id) && app()->bound(TenantContext::class)) {
                $schoolId = app(TenantContext::class)->schoolId();

                if ($schoolId) {
                    $model->school_id = $schoolId;
                }
            }
        });

        static::addGlobalScope('school', function (Builder $query) {
            $schoolId = app(TenantContext::class)->schoolId();

            if ($schoolId) {
                $query->where($query->getModel()->getTable().'.school_id', $schoolId);
            }
        });
    }

    public function scopeForCurrentSchool(Builder $query): Builder
    {
        $schoolId = app(TenantContext::class)->schoolId();

        if (! $schoolId) {
            return $query;
        }

        return $query->where($this->getTable().'.school_id', $schoolId);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
