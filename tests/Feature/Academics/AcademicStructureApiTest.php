<?php

namespace Tests\Feature\Academics;

use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AcademicStructureApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private School $school;

    private Campus $campus;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPermissions();

        $this->school = School::query()->create([
            'name' => 'Demo International School',
            'code' => 'DIS',
            'status' => 'active',
        ]);

        $this->campus = Campus::query()->create([
            'school_id' => $this->school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $this->user = User::query()->create([
            'name' => 'School Admin',
            'email' => 'school-admin@example.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $this->user->schools()->attach($this->school->id, [
            'role_context' => 'school_admin',
            'is_default' => true,
        ]);

        $this->user->assignRole('school_admin');

        Sanctum::actingAs($this->user);
    }

    public function test_academic_year_crud_routes_work(): void
    {
        $createResponse = $this->withTenant()->postJson('/api/v1/academic-years', [
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.name', '2026/2027');

        $academicYearId = $createResponse->json('data.id');

        $this->withTenant()
            ->getJson('/api/v1/academic-years')
            ->assertOk()
            ->assertJsonPath('data.0.id', $academicYearId);

        $this->withTenant()
            ->getJson("/api/v1/academic-years/{$academicYearId}")
            ->assertOk()
            ->assertJsonPath('data.id', $academicYearId);

        $this->withTenant()
            ->patchJson("/api/v1/academic-years/{$academicYearId}", [
                'name' => '2026 Academic Year',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '2026 Academic Year');

        $this->withTenant()
            ->deleteJson("/api/v1/academic-years/{$academicYearId}")
            ->assertOk();

        $this->assertSoftDeleted('academic_years', [
            'id' => $academicYearId,
        ]);
    }

    public function test_academic_term_class_level_class_arm_subject_and_teacher_assignment_routes_work(): void
    {
        $academicYear = AcademicYear::query()->create([
            'school_id' => $this->school->id,
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $termResponse = $this->withTenant()->postJson('/api/v1/academic-terms', [
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
            'status' => 'active',
        ]);

        $termResponse->assertCreated()
            ->assertJsonPath('data.name', 'Term 1');

        $classLevelResponse = $this->withTenant()->postJson('/api/v1/class-levels', [
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $classLevelResponse->assertCreated()
            ->assertJsonPath('data.code', 'B1');

        $classLevelId = $classLevelResponse->json('data.id');

        $classArmResponse = $this->withTenant()->postJson('/api/v1/class-arms', [
            'campus_id' => $this->campus->id,
            'class_level_id' => $classLevelId,
            'name' => 'Basic 1 A',
            'code' => 'A',
            'capacity' => 35,
            'status' => 'active',
        ]);

        $classArmResponse->assertCreated()
            ->assertJsonPath('data.code', 'A');

        $subjectResponse = $this->withTenant()->postJson('/api/v1/subjects', [
            'name' => 'Mathematics',
            'code' => 'MATH',
            'type' => 'core',
            'status' => 'active',
        ]);

        $subjectResponse->assertCreated()
            ->assertJsonPath('data.code', 'MATH');

        $teacher = User::query()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $teacher->schools()->attach($this->school->id, [
            'role_context' => 'teacher',
            'is_default' => false,
        ]);

        $assignmentResponse = $this->withTenant()->postJson('/api/v1/teacher-assignments', [
            'campus_id' => $this->campus->id,
            'academic_year_id' => $academicYear->id,
            'academic_term_id' => $termResponse->json('data.id'),
            'class_level_id' => $classLevelId,
            'class_arm_id' => $classArmResponse->json('data.id'),
            'subject_id' => $subjectResponse->json('data.id'),
            'teacher_user_id' => $teacher->id,
            'assignment_type' => 'subject_teacher',
            'starts_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $assignmentResponse->assertCreated()
            ->assertJsonPath('data.teacher_user_id', $teacher->id);

        $this->withTenant()
            ->getJson('/api/v1/teacher-assignments')
            ->assertOk()
            ->assertJsonPath('data.0.id', $assignmentResponse->json('data.id'));
    }

    public function test_academic_routes_require_tenant_header(): void
    {
        $this->getJson('/api/v1/academic-years')
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Missing tenant context.');
    }

    private function withTenant(): self
    {
        return $this->withHeaders([
            'X-School-ID' => $this->school->id,
            'Accept' => 'application/json',
        ]);
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
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $role = Role::query()->firstOrCreate([
            'name' => 'school_admin',
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($permissions);
    }
}
