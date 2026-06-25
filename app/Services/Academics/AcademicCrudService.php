<?php

namespace App\Services\Academics;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AcademicCrudService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function list(string $modelClass, array $filters = [], array $with = []): LengthAwarePaginator
    {
        /** @var class-string<Model> $modelClass */
        return $modelClass::query()
            ->with($with)
            ->forCurrentSchool()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'ILIKE', "%{$search}%");

                    if ($this->columnExists($query, 'code')) {
                        $query->orWhere('code', 'ILIKE', "%{$search}%");
                    }
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, string $id) => $query->where('academic_year_id', $id))
            ->when($filters['academic_term_id'] ?? null, fn (Builder $query, string $id) => $query->where('academic_term_id', $id))
            ->when($filters['campus_id'] ?? null, fn (Builder $query, string $id) => $query->where('campus_id', $id))
            ->when($filters['class_level_id'] ?? null, fn (Builder $query, string $id) => $query->where('class_level_id', $id))
            ->when($filters['class_arm_id'] ?? null, fn (Builder $query, string $id) => $query->where('class_arm_id', $id))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, string $id) => $query->where('subject_id', $id))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(string $modelClass, array $data, array $with = []): Model
    {
        return DB::transaction(function () use ($modelClass, $data, $with) {
            $schoolId = $this->tenantContext->requireSchoolId();

            $this->resetCurrentFlagsIfNeeded($modelClass, $schoolId, $data);

            /** @var Model $record */
            $record = $modelClass::query()->create([
                ...$data,
                'school_id' => $schoolId,
                'status' => $data['status'] ?? 'active',
            ]);

            return $record->load($with);
        });
    }

    public function update(Model $record, array $data, array $with = []): Model
    {
        return DB::transaction(function () use ($record, $data, $with) {
            $this->abortIfOutsideTenant($record);

            $this->resetCurrentFlagsIfNeeded($record::class, $record->school_id, $data, $record->getKey());

            $record->update($data);

            return $record->refresh()->load($with);
        });
    }

    public function delete(Model $record): void
    {
        DB::transaction(function () use ($record) {
            $this->abortIfOutsideTenant($record);
            $record->delete();
        });
    }

    private function abortIfOutsideTenant(Model $record): void
    {
        abort_unless(
            isset($record->school_id) && $record->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage this resource outside the current school.'
        );
    }

    private function resetCurrentFlagsIfNeeded(string $modelClass, string $schoolId, array $data, ?string $exceptId = null): void
    {
        if (! in_array($modelClass, [AcademicYear::class, AcademicTerm::class], true)) {
            return;
        }

        if (! (bool) ($data['is_current'] ?? false)) {
            return;
        }

        $query = $modelClass::query()
            ->where('school_id', $schoolId);

        if ($modelClass === AcademicTerm::class && ! empty($data['academic_year_id'])) {
            $query->where('academic_year_id', $data['academic_year_id']);
        }

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        $query->update(['is_current' => false]);
    }

    private function columnExists(Builder $query, string $column): bool
    {
        $model = $query->getModel();

        return in_array($column, $model->getFillable(), true);
    }
}
