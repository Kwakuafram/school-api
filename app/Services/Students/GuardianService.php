<?php

namespace App\Services\Students;

use App\Models\Guardian;
use App\Models\Student;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class GuardianService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Guardian::query()
            ->withCount('students')
            ->forCurrentSchool()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('first_name', 'ILIKE', "%{$search}%")
                        ->orWhere('middle_name', 'ILIKE', "%{$search}%")
                        ->orWhere('last_name', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%")
                        ->orWhere('phone', 'ILIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): Guardian
    {
        return DB::transaction(function () use ($data) {
            return Guardian::query()
                ->create([
                    ...$data,
                    'school_id' => $this->tenantContext->requireSchoolId(),
                    'status' => $data['status'] ?? 'active',
                ])
                ->load('students');
        });
    }

    public function update(Guardian $guardian, array $data): Guardian
    {
        return DB::transaction(function () use ($guardian, $data) {
            $this->abortIfGuardianOutsideTenant($guardian);

            $guardian->update($data);

            return $guardian->refresh()->load('students');
        });
    }

    public function delete(Guardian $guardian): void
    {
        DB::transaction(function () use ($guardian) {
            $this->abortIfGuardianOutsideTenant($guardian);

            $guardian->students()->detach();
            $guardian->delete();
        });
    }

    public function attachToStudent(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $this->abortIfStudentOutsideTenant($student);

            $guardian = Guardian::query()
                ->forCurrentSchool()
                ->whereKey($data['guardian_id'])
                ->firstOrFail();

            if ((bool) ($data['is_primary'] ?? false)) {
                $this->clearPrimaryGuardian($student);
            }

            $student->guardians()->syncWithoutDetaching([
                $guardian->id => [
                    'relationship' => $data['relationship'],
                    'is_primary' => (bool) ($data['is_primary'] ?? false),
                    'can_pick_up' => (bool) ($data['can_pick_up'] ?? false),
                    'receives_sms' => (bool) ($data['receives_sms'] ?? true),
                    'receives_email' => (bool) ($data['receives_email'] ?? true),
                ],
            ]);

            return $student->refresh()->load(['campus', 'guardians', 'currentEnrollment']);
        });
    }

    public function updateStudentGuardian(Student $student, Guardian $guardian, array $data): Student
    {
        return DB::transaction(function () use ($student, $guardian, $data) {
            $this->abortIfStudentOutsideTenant($student);
            $this->abortIfGuardianOutsideTenant($guardian);

            abort_unless(
                $student->guardians()->whereKey($guardian->id)->exists(),
                404,
                'This guardian is not attached to the selected student.'
            );

            if ((bool) ($data['is_primary'] ?? false)) {
                $this->clearPrimaryGuardian($student);
            }

            $student->guardians()->updateExistingPivot($guardian->id, $data);

            return $student->refresh()->load(['campus', 'guardians', 'currentEnrollment']);
        });
    }

    public function detachFromStudent(Student $student, Guardian $guardian): Student
    {
        return DB::transaction(function () use ($student, $guardian) {
            $this->abortIfStudentOutsideTenant($student);
            $this->abortIfGuardianOutsideTenant($guardian);

            $student->guardians()->detach($guardian->id);

            return $student->refresh()->load(['campus', 'guardians', 'currentEnrollment']);
        });
    }

    private function clearPrimaryGuardian(Student $student): void
    {
        $student->guardians()
            ->newPivotStatement()
            ->where('student_id', $student->id)
            ->update(['is_primary' => false]);
    }

    private function abortIfGuardianOutsideTenant(Guardian $guardian): void
    {
        abort_unless(
            $guardian->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage a guardian outside the current school.'
        );
    }

    private function abortIfStudentOutsideTenant(Student $student): void
    {
        abort_unless(
            $student->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage a student outside the current school.'
        );
    }
}
