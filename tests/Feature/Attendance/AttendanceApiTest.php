<?php

namespace Tests\Feature\Attendance;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\AttendanceSession;
use App\Models\Campus;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceApiTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Campus $campus;

    private AcademicYear $academicYear;

    private AcademicTerm $academicTerm;

    private ClassLevel $classLevel;

    private ClassArm $classArm;

    private Student $student1;

    private Student $student2;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissions();

        $this->school = School::query()->create([
            'name' => 'Test School',
            'code' => 'TST',
            'status' => 'active',
        ]);

        $this->campus = Campus::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $this->academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $this->academicTerm = AcademicTerm::query()->create([
            'school_id' => $this->school->id,
            'academic_year_id' => $this->academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
            'status' => 'active',
        ]);

        $this->classLevel = ClassLevel::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $this->classArm = ClassArm::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'class_level_id' => $this->classLevel->id,
            'name' => 'Basic 1 A',
            'code' => 'A',
            'status' => 'active',
        ]);

        $this->student1 = Student::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'Kwame',
            'last_name' => 'Mensah',
            'status' => 'active',
        ]);

        $this->student2 = Student::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'admission_number' => 'ADM-002',
            'first_name' => 'Ama',
            'last_name' => 'Asante',
            'status' => 'active',
        ]);

        $this->admin = $this->makeUser('school_admin');
        Sanctum::actingAs($this->admin);
    }

    public function test_can_create_attendance_session(): void
    {
        $response = $this->withTenant()->postJson('/api/v1/attendance/sessions', [
            'class_arm_id' => $this->classArm->id,
            'academic_year_id' => $this->academicYear->id,
            'academic_term_id' => $this->academicTerm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.class_arm_id', $this->classArm->id)
            ->assertJsonPath('data.class_level_id', $this->classLevel->id)
            ->assertJsonPath('data.campus_id', $this->campus->id)
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.session_date', '2026-09-15')
            ->assertJsonPath('data.period', 'morning');
    }

    public function test_can_list_attendance_sessions(): void
    {
        AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'academic_term_id' => $this->academicTerm->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'open',
        ]);

        $response = $this->withTenant()->getJson('/api/v1/attendance/sessions');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.class_arm_id', $this->classArm->id);
    }

    public function test_can_filter_sessions_by_status(): void
    {
        AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'open',
        ]);

        $this->withTenant()
            ->getJson('/api/v1/attendance/sessions?status=submitted')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->withTenant()
            ->getJson('/api/v1/attendance/sessions?status=open')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_can_create_attendance_session_without_class_arm(): void
    {
        $response = $this->withTenant()->postJson('/api/v1/attendance/sessions', [
            'class_level_id' => $this->classLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'academic_term_id' => $this->academicTerm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.class_level_id', $this->classLevel->id)
            ->assertJsonPath('data.class_arm_id', null)
            ->assertJsonPath('data.status', 'open');
    }

    public function test_cannot_create_duplicate_session_without_class_arm(): void
    {
        $payload = [
            'class_level_id' => $this->classLevel->id,
            'academic_year_id' => $this->academicYear->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
        ];

        $this->withTenant()->postJson('/api/v1/attendance/sessions', $payload)->assertCreated();

        $this->withTenant()
            ->postJson('/api/v1/attendance/sessions', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('errors.session_date.0', 'An attendance session already exists for this class, date, and period.');
    }

    public function test_cannot_create_duplicate_session_for_same_class_arm_date_and_period(): void
    {
        $payload = [
            'class_arm_id' => $this->classArm->id,
            'academic_year_id' => $this->academicYear->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
        ];

        $this->withTenant()->postJson('/api/v1/attendance/sessions', $payload)->assertCreated();

        $this->withTenant()
            ->postJson('/api/v1/attendance/sessions', $payload)
            ->assertUnprocessable()
            ->assertJsonPath('errors.session_date.0', 'An attendance session already exists for this class, date, and period.');
    }

    public function test_can_submit_attendance_for_session(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'open',
        ]);

        $response = $this->withTenant()->patchJson("/api/v1/attendance/sessions/{$session->id}/submit", [
            'attendances' => [
                ['student_id' => $this->student1->id, 'status' => 'present'],
                ['student_id' => $this->student2->id, 'status' => 'absent', 'notes' => 'Sick'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonCount(2, 'data.student_attendances');

        $this->assertDatabaseHas('student_attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $this->student1->id,
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('student_attendances', [
            'attendance_session_id' => $session->id,
            'student_id' => $this->student2->id,
            'status' => 'absent',
        ]);
    }

    public function test_cannot_submit_already_submitted_session(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'submitted',
        ]);

        $this->withTenant()
            ->patchJson("/api/v1/attendance/sessions/{$session->id}/submit", [
                'attendances' => [
                    ['student_id' => $this->student1->id, 'status' => 'present'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'This session has already been submitted.');
    }

    public function test_can_approve_submitted_session(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'submitted',
        ]);

        $this->withTenant()
            ->patchJson("/api/v1/attendance/sessions/{$session->id}/approve")
            ->assertOk()
            ->assertJsonPath('data.status', 'approved');

        $this->assertDatabaseHas('attendance_sessions', [
            'id' => $session->id,
            'status' => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }

    public function test_cannot_approve_open_session(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'open',
        ]);

        $this->withTenant()
            ->patchJson("/api/v1/attendance/sessions/{$session->id}/approve")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Only submitted sessions can be approved.');
    }

    public function test_teacher_cannot_approve_session(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'submitted',
        ]);

        Sanctum::actingAs($this->makeUser('teacher'));

        $this->withTenant()
            ->patchJson("/api/v1/attendance/sessions/{$session->id}/approve")
            ->assertForbidden();
    }

    public function test_can_view_student_attendance_history(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'submitted',
        ]);

        $session->studentAttendances()->create([
            'school_id' => $this->school->id,
            'student_id' => $this->student1->id,
            'status' => 'present',
        ]);

        $response = $this->withTenant()
            ->getJson("/api/v1/students/{$this->student1->id}/attendance");

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.student_id', $this->student1->id)
            ->assertJsonPath('data.0.status', 'present');
    }

    public function test_approved_session_cannot_be_deleted(): void
    {
        $session = AttendanceSession::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'academic_year_id' => $this->academicYear->id,
            'class_level_id' => $this->classLevel->id,
            'class_arm_id' => $this->classArm->id,
            'session_date' => '2026-09-15',
            'period' => 'morning',
            'status' => 'approved',
        ]);

        $this->withTenant()
            ->deleteJson("/api/v1/attendance/sessions/{$session->id}")
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Approved sessions cannot be deleted.');
    }

    public function test_attendance_routes_require_tenant_header(): void
    {
        $this->getJson('/api/v1/attendance/sessions')->assertUnprocessable();
    }

    private function withTenant(): self
    {
        return $this->withHeaders([
            'X-School-ID' => $this->school->id,
            'Accept' => 'application/json',
        ]);
    }

    private function makeUser(string $role): User
    {
        $user = User::query()->create([
            'name' => ucfirst($role).' User',
            'email' => $role.'-'.uniqid().'@example.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $user->schools()->attach($this->school->id, [
            'role_context' => $role,
            'is_default' => true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    private function seedPermissions(): void
    {
        $permissions = [
            'attendance.view',
            'attendance.take',
            'attendance.approve',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->makeRole('school_admin', ['attendance.view', 'attendance.take', 'attendance.approve']);
        $this->makeRole('teacher', ['attendance.view', 'attendance.take']);
        $this->makeRole('principal', ['attendance.view', 'attendance.approve']);
    }

    private function makeRole(string $name, array $permissionNames): void
    {
        $role = Role::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $role->syncPermissions($permissionNames);
    }
}
