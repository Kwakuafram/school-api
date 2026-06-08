<?php

namespace App\Services;

use App\Models\User;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Services\AuditLogService;

class UserService
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly AuditLogService $auditLogService
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        $schoolId = $this->tenantContext->requireSchoolId();

        return User::query()
            ->with(['roles', 'schools'])
            ->whereHas('schools', function (Builder $query) use ($schoolId) {
                $query->where('schools.id', $schoolId);
            })
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $schoolId = $this->tenantContext->requireSchoolId();

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => $data['status'] ?? 'active',
            ]);

            if (! empty($data['roles'])) {
                $user->syncRoles($data['roles']);
            }

            $user->schools()->syncWithoutDetaching([
                $schoolId => [
                    'role_context' => $data['role_context'] ?? null,
                    'is_default' => (bool) ($data['is_default_school'] ?? true),
                ],
            ]);

            return $user->load(['roles', 'schools']);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $this->abortIfUserOutsideTenant($user);

            $rolesProvided = array_key_exists('roles', $data);
            $roles = $data['roles'] ?? [];

            $payload = Arr::except($data, [
                'roles',
                'password_confirmation',
                'role_context',
                'is_default_school',
            ]);

            if (array_key_exists('password', $payload) && empty($payload['password'])) {
                unset($payload['password']);
            }

            $user->update($payload);

            if ($rolesProvided) {
                $user->syncRoles($roles);
            }

            return $user->refresh()->load(['roles', 'schools']);
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(function () use ($user) {
            $this->abortIfUserOutsideTenant($user);

            $user->tokens()->delete();
            $user->schools()->detach();
            $user->delete();
        });
    }

 public function activate(User $user): User
{
    $this->abortIfUserOutsideTenant($user);

    $oldValues = [
        'status' => $user->status,
    ];

    $user->update(['status' => 'active']);

    $this->auditLogService->record(
        action: 'user.activated',
        auditable: $user,
        oldValues: $oldValues,
        newValues: [
            'status' => 'active',
        ],
    );

    return $user->refresh()->load(['roles', 'schools']);
}

 public function suspend(User $user): User
{
    $this->abortIfUserOutsideTenant($user);

    $oldValues = [
        'status' => $user->status,
    ];

    $user->update(['status' => 'suspended']);
    $user->tokens()->delete();

    $this->auditLogService->record(
        action: 'user.suspended',
        auditable: $user,
        oldValues: $oldValues,
        newValues: [
            'status' => 'suspended',
        ],
        metadata: [
            'tokens_revoked' => true,
        ],
    );

    return $user->refresh()->load(['roles', 'schools']);
}

    private function abortIfUserOutsideTenant(User $user): void
    {
        $schoolId = $this->tenantContext->requireSchoolId();

        $belongsToSchool = $user->schools()
            ->where('schools.id', $schoolId)
            ->exists();

        abort_unless(
            $belongsToSchool,
            403,
            'You cannot manage a user outside the current school.'
        );
    }
}