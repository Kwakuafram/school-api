<?php

namespace Tests\Feature\Auth;

use App\Models\Campus;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private Campus $campus;

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
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/students')->assertUnauthorized();
        $this->getJson('/api/v1/academic-years')->assertUnauthorized();
        $this->getJson('/api/v1/guardians')->assertUnauthorized();
    }

    public function test_missing_tenant_header_returns_422(): void
    {
        Sanctum::actingAs($this->makeUser('school_admin'));

        $this->getJson('/api/v1/students')->assertUnprocessable();
        $this->getJson('/api/v1/academic-years')->assertUnprocessable();
        $this->getJson('/api/v1/guardians')->assertUnprocessable();
    }

    public function test_user_from_different_school_is_forbidden(): void
    {
        $otherSchool = School::query()->create([
            'name' => 'Other School',
            'code' => 'OTH',
            'status' => 'active',
        ]);

        // User belongs to $this->school only
        Sanctum::actingAs($this->makeUser('school_admin'));

        $this->withHeaders(['X-School-ID' => $otherSchool->id, 'Accept' => 'application/json'])
            ->getJson('/api/v1/students')
            ->assertForbidden();
    }

    public function test_teacher_cannot_create_academic_years(): void
    {
        Sanctum::actingAs($this->makeUser('teacher'));

        $this->withTenant()
            ->postJson('/api/v1/academic-years', [
                'name' => '2026/2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-07-31',
            ])
            ->assertForbidden();
    }

    public function test_teacher_cannot_create_class_levels(): void
    {
        Sanctum::actingAs($this->makeUser('teacher'));

        $this->withTenant()
            ->postJson('/api/v1/class-levels', [
                'name' => 'Basic 1',
                'code' => 'B1',
            ])
            ->assertForbidden();
    }

    public function test_teacher_cannot_delete_students(): void
    {
        $student = Student::query()->create([
            'school_id' => $this->school->id,
            'campus_id' => $this->campus->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'status' => 'active',
        ]);

        Sanctum::actingAs($this->makeUser('teacher'));

        $this->withTenant()
            ->deleteJson("/api/v1/students/{$student->id}")
            ->assertForbidden();
    }

    public function test_student_role_cannot_view_students(): void
    {
        Sanctum::actingAs($this->makeUser('student'));

        $this->withTenant()
            ->getJson('/api/v1/students')
            ->assertForbidden();
    }

    public function test_student_role_cannot_view_guardians(): void
    {
        Sanctum::actingAs($this->makeUser('student'));

        $this->withTenant()
            ->getJson('/api/v1/guardians')
            ->assertForbidden();
    }

    public function test_school_admin_can_view_guardians(): void
    {
        Sanctum::actingAs($this->makeUser('school_admin'));

        $this->withTenant()
            ->getJson('/api/v1/guardians')
            ->assertOk();
    }

    public function test_school_admin_can_create_guardians(): void
    {
        Sanctum::actingAs($this->makeUser('school_admin'));

        $this->withTenant()
            ->postJson('/api/v1/guardians', [
                'first_name' => 'Ama',
                'last_name' => 'Mensah',
                'phone' => '+233000000001',
                'status' => 'active',
            ])
            ->assertCreated();
    }

    public function test_school_admin_can_view_academic_years(): void
    {
        Sanctum::actingAs($this->makeUser('school_admin'));

        $this->withTenant()
            ->getJson('/api/v1/academic-years')
            ->assertOk();
    }

    public function test_teacher_can_view_academic_years(): void
    {
        Sanctum::actingAs($this->makeUser('teacher'));

        $this->withTenant()
            ->getJson('/api/v1/academic-years')
            ->assertOk();
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
            'academics.view',
            'academics.create',
            'academics.update',
            'academics.delete',
            'teacher_assignments.view',
            'teacher_assignments.create',
            'teacher_assignments.update',
            'teacher_assignments.delete',
            'students.view',
            'students.create',
            'students.update',
            'students.delete',
            'students.promote',
            'guardians.view',
            'guardians.create',
            'guardians.update',
            'guardians.delete',
            'students.guardians.manage',
            'attendance.view',
            'attendance.take',
            'results.view',
            'results.enter',
            'dashboard.view',
        ];

        foreach ($permissions as $name) {
            Permission::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->makeRole('school_admin', [
            'academics.view', 'academics.create', 'academics.update', 'academics.delete',
            'teacher_assignments.view', 'teacher_assignments.create', 'teacher_assignments.update', 'teacher_assignments.delete',
            'students.view', 'students.create', 'students.update', 'students.delete', 'students.promote',
            'guardians.view', 'guardians.create', 'guardians.update', 'guardians.delete', 'students.guardians.manage',
            'attendance.view', 'attendance.take',
            'results.view', 'results.enter',
            'dashboard.view',
        ]);

        $this->makeRole('teacher', [
            'academics.view',
            'teacher_assignments.view',
            'students.view',
            'attendance.view', 'attendance.take',
            'results.view', 'results.enter',
            'dashboard.view',
        ]);

        $this->makeRole('student', [
            'attendance.view',
            'results.view',
            'dashboard.view',
        ]);
    }

    private function makeRole(string $name, array $permissionNames): void
    {
        $role = Role::query()->firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        $role->syncPermissions($permissionNames);
    }
}
