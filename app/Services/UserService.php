<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->with(['roles', 'schools'])
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
            $roles = $data['roles'] ?? [];

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'status' => $data['status'] ?? 'active',
            ]);

            if (! empty($roles)) {
                $user->syncRoles($roles);
            }

            if (! empty($data['school_id'])) {
                $user->schools()->syncWithoutDetaching([
                    $data['school_id'] => [
                        'role_context' => $data['role_context'] ?? null,
                        'is_default' => (bool) ($data['is_default_school'] ?? true),
                    ],
                ]);
            }

            return $user->load(['roles', 'schools']);
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $rolesProvided = array_key_exists('roles', $data);
            $roles = $data['roles'] ?? [];

            $payload = Arr::except($data, [
                'roles',
                'password_confirmation',
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
            $user->tokens()->delete();
            $user->schools()->detach();
            $user->delete();
        });
    }

    public function activate(User $user): User
    {
        $user->update(['status' => 'active']);

        return $user->refresh()->load(['roles', 'schools']);
    }

    public function suspend(User $user): User
    {
        $user->update(['status' => 'suspended']);
        $user->tokens()->delete();

        return $user->refresh()->load(['roles', 'schools']);
    }
}