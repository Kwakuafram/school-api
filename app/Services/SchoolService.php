<?php

namespace App\Services;

use App\Models\School;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SchoolService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return School::query()
            ->withCount(['campuses', 'users'])
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('code', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): School
    {
        return DB::transaction(function () use ($data) {
            return School::create([
                'name' => $data['name'],
                'code' => $data['code'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'country' => $data['country'] ?? null,
                'region' => $data['region'] ?? null,
                'city' => $data['city'] ?? null,
                'address' => $data['address'] ?? null,
                'status' => $data['status'] ?? 'active',
                'settings' => $data['settings'] ?? null,
            ]);
        });
    }

    public function update(School $school, array $data): School
    {
        return DB::transaction(function () use ($school, $data) {
            $school->update($data);

            return $school->refresh();
        });
    }

    public function delete(School $school): void
    {
        DB::transaction(function () use ($school) {
            $school->delete();
        });
    }
}