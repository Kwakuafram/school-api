<?php

namespace App\Services\Students;

use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class StudentService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return Student::query()
            ->with(['campus', 'currentEnrollment'])
            ->forCurrentSchool()
            ->when($filters['campus_id'] ?? null, fn (Builder $query, string $campusId) => $query->where('campus_id', $campusId))
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('first_name', 'ILIKE', "%{$search}%")
                        ->orWhere('middle_name', 'ILIKE', "%{$search}%")
                        ->orWhere('last_name', 'ILIKE', "%{$search}%")
                        ->orWhere('admission_number', 'ILIKE', "%{$search}%")
                        ->orWhere('email', 'ILIKE', "%{$search}%");
                });
            })
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            $schoolId = $this->tenantContext->requireSchoolId();

            $student = Student::query()->create([
                ...Arr::except($data, ['enrollment']),
                'school_id' => $schoolId,
            ]);

            if (! empty($data['enrollment'])) {
                $enrollment = StudentEnrollment::query()->create([
                    'school_id' => $schoolId,
                    'campus_id' => $student->campus_id,
                    'student_id' => $student->id,
                    'academic_year_id' => $data['enrollment']['academic_year_id'],
                    'academic_term_id' => $data['enrollment']['academic_term_id'] ?? null,
                    'class_level_id' => $data['enrollment']['class_level_id'] ?? null,
                    'class_arm_id' => $data['enrollment']['class_arm_id'] ?? null,
                    'enrolled_at' => $data['enrollment']['enrolled_at'] ?? now()->toDateString(),
                    'status' => 'active',
                ]);

                $student->update([
                    'current_enrollment_id' => $enrollment->id,
                ]);
            }

            return $student->load(['campus', 'guardians', 'currentEnrollment']);
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $this->abortIfOutsideTenant($student);

            $student->update($data);

            return $student->refresh()->load(['campus', 'guardians', 'currentEnrollment']);
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $this->abortIfOutsideTenant($student);

            $student->delete();
        });
    }

    private function abortIfOutsideTenant(Student $student): void
    {
        abort_unless(
            $student->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage a student outside the current school.'
        );
    }
}
