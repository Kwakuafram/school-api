<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use App\Services\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AuditLogController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $schoolId = $this->tenantContext->requireSchoolId();

        $logs = AuditLog::query()
            ->with('user')
            ->where('school_id', $schoolId)
            ->when($request->query('action'), function (Builder $query, string $action) {
                $query->where('action', $action);
            })
            ->when($request->query('user_id'), function (Builder $query, string $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->query('auditable_type'), function (Builder $query, string $type) {
                $query->where('auditable_type', $type);
            })
            ->when($request->query('auditable_id'), function (Builder $query, string $id) {
                $query->where('auditable_id', $id);
            })
            ->latest()
            ->paginate((int) $request->query('per_page', 20));

        return AuditLogResource::collection($logs);
    }

    public function show(AuditLog $auditLog): AuditLogResource
    {
        $schoolId = $this->tenantContext->requireSchoolId();

        abort_unless(
            $auditLog->school_id === $schoolId,
            403,
            'You cannot view audit logs outside the current school.'
        );

        return new AuditLogResource($auditLog->load('user'));
    }
}
