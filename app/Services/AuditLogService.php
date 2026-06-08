<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Services\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function record(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = [],
        ?Request $request = null,
        ?string $schoolId = null,
        ?string $campusId = null,
        ?string $userId = null,
    ): AuditLog {
        $request ??= request();

        return AuditLog::query()->create([
            'school_id' => $schoolId ?? $this->resolveSchoolId($auditable),
            'campus_id' => $campusId ?? $this->resolveCampusId($auditable),
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'metadata' => $metadata ?: null,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function resolveSchoolId(?Model $auditable): ?string
    {
        if ($auditable && isset($auditable->school_id)) {
            return $auditable->school_id;
        }

        if ($auditable instanceof \App\Models\School) {
            return $auditable->id;
        }

        return $this->tenantContext->schoolId();
    }

    private function resolveCampusId(?Model $auditable): ?string
    {
        if ($auditable && isset($auditable->campus_id)) {
            return $auditable->campus_id;
        }

        if ($auditable instanceof \App\Models\Campus) {
            return $auditable->id;
        }

        return $this->tenantContext->campusId();
    }

    private function sanitize(array $values): ?array
    {
        if ($values === []) {
            return null;
        }

        return Arr::except($values, [
            'password',
            'remember_token',
            'token',
            'secret',
            'api_key',
            'access_token',
            'refresh_token',
        ]);
    }
}