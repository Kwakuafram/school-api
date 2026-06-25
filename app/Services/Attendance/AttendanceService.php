<?php

namespace App\Services\Attendance;

use App\Models\AttendanceSession;
use App\Models\ClassArm;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function list(array $filters = []): LengthAwarePaginator
    {
        return AttendanceSession::query()
            ->with(['classLevel', 'classArm', 'academicYear', 'academicTerm'])
            ->forCurrentSchool()
            ->when($filters['class_arm_id'] ?? null, fn (Builder $q, string $v) => $q->where('class_arm_id', $v))
            ->when($filters['class_level_id'] ?? null, fn (Builder $q, string $v) => $q->where('class_level_id', $v))
            ->when($filters['academic_year_id'] ?? null, fn (Builder $q, string $v) => $q->where('academic_year_id', $v))
            ->when($filters['academic_term_id'] ?? null, fn (Builder $q, string $v) => $q->where('academic_term_id', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when($filters['session_date'] ?? null, fn (Builder $q, string $v) => $q->whereDate('session_date', $v))
            ->latest('session_date')
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(array $data): AttendanceSession
    {
        return DB::transaction(function () use ($data) {
            $schoolId = $this->tenantContext->requireSchoolId();

            $classLevelId = $data['class_level_id'] ?? null;
            $campusId = $data['campus_id'] ?? null;

            if (! empty($data['class_arm_id'])) {
                $classArm = ClassArm::query()
                    ->forCurrentSchool()
                    ->findOrFail($data['class_arm_id']);

                $classLevelId = $classArm->class_level_id;
                $campusId = $classArm->campus_id;
            }

            $session = AttendanceSession::query()->create([
                'school_id' => $schoolId,
                'campus_id' => $campusId,
                'class_level_id' => $classLevelId,
                'class_arm_id' => $data['class_arm_id'] ?? null,
                'academic_year_id' => $data['academic_year_id'],
                'academic_term_id' => $data['academic_term_id'] ?? null,
                'session_date' => $data['session_date'],
                'period' => $data['period'] ?? 'morning',
                'status' => 'open',
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
            ]);

            return $session->load(['classLevel', 'classArm', 'academicYear', 'academicTerm']);
        });
    }

    public function submit(AttendanceSession $session, array $attendances): AttendanceSession
    {
        $this->abortIfOutsideTenant($session);

        abort_if(
            $session->status !== 'open',
            422,
            'This session has already been submitted.'
        );

        return DB::transaction(function () use ($session, $attendances) {
            foreach ($attendances as $item) {
                StudentAttendance::query()->updateOrCreate(
                    [
                        'attendance_session_id' => $session->id,
                        'student_id' => $item['student_id'],
                    ],
                    [
                        'school_id' => $session->school_id,
                        'status' => $item['status'],
                        'notes' => $item['notes'] ?? null,
                        'recorded_by' => Auth::id(),
                    ]
                );
            }

            $session->update([
                'status' => 'submitted',
                'submitted_by' => Auth::id(),
                'submitted_at' => now(),
            ]);

            return $session->refresh()->load([
                'classLevel', 'classArm', 'academicYear', 'academicTerm',
                'studentAttendances.student',
            ]);
        });
    }

    public function approve(AttendanceSession $session): AttendanceSession
    {
        $this->abortIfOutsideTenant($session);

        abort_if(
            $session->status !== 'submitted',
            422,
            'Only submitted sessions can be approved.'
        );

        return DB::transaction(function () use ($session) {
            $session->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return $session->refresh();
        });
    }

    public function delete(AttendanceSession $session): void
    {
        $this->abortIfOutsideTenant($session);

        abort_if(
            $session->status === 'approved',
            422,
            'Approved sessions cannot be deleted.'
        );

        DB::transaction(fn () => $session->delete());
    }

    public function listForStudent(Student $student, array $filters = []): LengthAwarePaginator
    {
        abort_unless(
            $student->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot view attendance for a student outside the current school.'
        );

        return StudentAttendance::query()
            ->with(['session' => fn ($q) => $q->with(['classArm', 'classLevel', 'academicYear', 'academicTerm'])])
            ->where('student_id', $student->id)
            ->forCurrentSchool()
            ->when($filters['status'] ?? null, fn (Builder $q, string $v) => $q->where('status', $v))
            ->when(
                $filters['academic_year_id'] ?? null,
                fn (Builder $q, string $v) => $q->whereHas(
                    'session',
                    fn (Builder $q) => $q->where('academic_year_id', $v)
                )
            )
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    private function abortIfOutsideTenant(AttendanceSession $session): void
    {
        abort_unless(
            $session->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage this session outside the current school.'
        );
    }
}
