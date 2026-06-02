<?php

namespace App\Services;

use App\Models\Campus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class CampusService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Campus::query()
            ->with(['school'])
            ->when($filters['school_id'] ?? null, fn (Builder $query, string $schoolId) => $query->where('school_id', $schoolId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('code', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): Campus
    {
        return DB::transaction(function () use ($data) {
            if ((bool) ($data['is_main'] ?? false)) {
                Campus::where('school_id', $data['school_id'])->update(['is_main' => false]);
            }

            return Campus::create([
                'school_id' => $data['school_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'country' => $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'is_main' => (bool) ($data['is_main'] ?? false),
                'status' => $data['status'] ?? 'active',
                'settings' => $data['settings'] ?? null,
            ])->load('school');
        });
    }

    public function update(Campus $campus, array $data): Campus
    {
        return DB::transaction(function () use ($campus, $data) {
            if ((bool) ($data['is_main'] ?? false)) {
                Campus::where('school_id', $data['school_id'] ?? $campus->school_id)
                    ->where('id', '!=', $campus->id)
                    ->update(['is_main' => false]);
            }

            $campus->update($data);

            return $campus->refresh()->load('school');
        });
    }

    public function delete(Campus $campus): void
    {
        DB::transaction(function () use ($campus) {
            $campus->delete();
        });
    }
}