<?php

namespace App\Models\Traits;

use App\Services\AuditLogService;

trait RecordsAuditLogs
{
    protected static function bootRecordsAuditLogs(): void
    {
        static::created(function ($model) {
            if (! $model->shouldRecordAuditLog()) {
                return;
            }

            app(AuditLogService::class)->record(
                action: $model->auditActionName('created'),
                auditable: $model,
                newValues: $model->getAttributes(),
            );
        });

        static::updated(function ($model) {
            if (! $model->shouldRecordAuditLog()) {
                return;
            }

            $changes = $model->getChanges();

            unset($changes['updated_at']);

            if ($changes === []) {
                return;
            }

            app(AuditLogService::class)->record(
                action: $model->auditActionName('updated'),
                auditable: $model,
                oldValues: array_intersect_key($model->getOriginal(), $changes),
                newValues: $changes,
            );
        });

        static::deleted(function ($model) {
            if (! $model->shouldRecordAuditLog()) {
                return;
            }

            app(AuditLogService::class)->record(
                action: $model->auditActionName('deleted'),
                auditable: $model,
                oldValues: $model->getOriginal(),
            );
        });
    }

    public function shouldRecordAuditLog(): bool
    {
        return true;
    }

    public function auditActionName(string $event): string
    {
        $base = class_basename($this);

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $base)).'.'.$event;
    }
}
