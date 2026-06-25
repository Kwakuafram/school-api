#!/usr/bin/env sh
set -eu

echo "Adding Academic Structure routes and tests..."

mkdir -p tests/Feature/Academics

python3 - <<'PY'
from pathlib import Path

path = Path("routes/api.php")
if not path.exists():
    raise SystemExit("routes/api.php not found")

text = path.read_text()

imports = [
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\AcademicTermController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\AcademicYearController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\ClassArmController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\ClassLevelController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\SubjectController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\TeacherAssignmentController;",
]

for import_line in reversed(imports):
    if import_line not in text:
        text = text.replace(
            "use Illuminate\\Support\\Facades\\Route;",
            import_line + "\nuse Illuminate\\Support\\Facades\\Route;",
        )

academic_routes = """
            Route::middleware('permission:academics.view')->get('academic-years', [AcademicYearController::class, 'index']);
            Route::middleware('permission:academics.create')->post('academic-years', [AcademicYearController::class, 'store']);
            Route::middleware('permission:academics.view')->get('academic-years/{academicYear}', [AcademicYearController::class, 'show']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'academic-years/{academicYear}', [AcademicYearController::class, 'update']);
            Route::middleware('permission:academics.delete')->delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy']);

            Route::middleware('permission:academics.view')->get('academic-terms', [AcademicTermController::class, 'index']);
            Route::middleware('permission:academics.create')->post('academic-terms', [AcademicTermController::class, 'store']);
            Route::middleware('permission:academics.view')->get('academic-terms/{academicTerm}', [AcademicTermController::class, 'show']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'academic-terms/{academicTerm}', [AcademicTermController::class, 'update']);
            Route::middleware('permission:academics.delete')->delete('academic-terms/{academicTerm}', [AcademicTermController::class, 'destroy']);

            Route::middleware('permission:academics.view')->get('class-levels', [ClassLevelController::class, 'index']);
            Route::middleware('permission:academics.create')->post('class-levels', [ClassLevelController::class, 'store']);
            Route::middleware('permission:academics.view')->get('class-levels/{classLevel}', [ClassLevelController::class, 'show']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'class-levels/{classLevel}', [ClassLevelController::class, 'update']);
            Route::middleware('permission:academics.delete')->delete('class-levels/{classLevel}', [ClassLevelController::class, 'destroy']);

            Route::middleware('permission:academics.view')->get('class-arms', [ClassArmController::class, 'index']);
            Route::middleware('permission:academics.create')->post('class-arms', [ClassArmController::class, 'store']);
            Route::middleware('permission:academics.view')->get('class-arms/{classArm}', [ClassArmController::class, 'show']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'class-arms/{classArm}', [ClassArmController::class, 'update']);
            Route::middleware('permission:academics.delete')->delete('class-arms/{classArm}', [ClassArmController::class, 'destroy']);

            Route::middleware('permission:academics.view')->get('subjects', [SubjectController::class, 'index']);
            Route::middleware('permission:academics.create')->post('subjects', [SubjectController::class, 'store']);
            Route::middleware('permission:academics.view')->get('subjects/{subject}', [SubjectController::class, 'show']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'subjects/{subject}', [SubjectController::class, 'update']);
            Route::middleware('permission:academics.delete')->delete('subjects/{subject}', [SubjectController::class, 'destroy']);

            Route::middleware('permission:teacher_assignments.view')->get('teacher-assignments', [TeacherAssignmentController::class, 'index']);
            Route::middleware('permission:teacher_assignments.create')->post('teacher-assignments', [TeacherAssignmentController::class, 'store']);
            Route::middleware('permission:teacher_assignments.view')->get('teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'show']);
            Route::middleware('permission:teacher_assignments.update')->match(['put', 'patch'], 'teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'update']);
            Route::middleware('permission:teacher_assignments.delete')->delete('teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'destroy']);
"""

if "academic-years" not in text:
    marker = "            Route::middleware('permission:audit_logs.view')->get('audit-logs'"
    if marker in text:
        text = text.replace(marker, academic_routes + "\n" + marker)
    else:
        marker = "        });\n    });\n});"
        if marker not in text:
            raise SystemExit("Could not find tenant route group insertion point in routes/api.php")
        text = text.replace(marker, academic_routes + "\n        });\n    });\n});")

path.write_text(text)
PY

cat > tests/Feature/Academics/AcademicStructureApiTest.php <<'PHP'
<?php

namespace Tests\Feature\Academics;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherAssignment;
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

    public function test_academic_term_class_level_class_arm_subject_and_assignment_routes_work(): void
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
PHP

cat > tests/Feature/Models/AcademicStructureModelsTest.php <<'PHP'
<?php

namespace Tests\Feature\Models;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_structure_models_can_be_created_and_related(): void
    {
        $school = School::query()->create([
            'name' => 'Demo School',
            'code' => 'DIS',
            'status' => 'active',
        ]);

        $campus = Campus::query()->create([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $teacher = User::query()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $teacher->schools()->attach($school->id, [
            'role_context' => 'teacher',
            'is_default' => true,
        ]);

        $academicYear = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $term = AcademicTerm::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
            'status' => 'active',
        ]);

        $level = ClassLevel::query()->create([
            'school_id' => $school->id,
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $arm = ClassArm::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'class_level_id' => $level->id,
            'name' => 'Basic 1 A',
            'code' => 'A',
            'capacity' => 35,
            'status' => 'active',
        ]);

        $subject = Subject::query()->create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'type' => 'core',
            'status' => 'active',
        ]);

        $assignment = TeacherAssignment::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'academic_year_id' => $academicYear->id,
            'academic_term_id' => $term->id,
            'class_level_id' => $level->id,
            'class_arm_id' => $arm->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'assignment_type' => 'subject_teacher',
            'starts_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $this->assertSame($academicYear->id, $term->academicYear->id);
        $this->assertSame($level->id, $arm->classLevel->id);
        $this->assertSame($campus->id, $arm->campus->id);
        $this->assertSame($teacher->id, $assignment->teacher->id);
        $this->assertSame($subject->id, $assignment->subject->id);
        $this->assertSame($arm->id, $assignment->classArm->id);
    }
}
PHP

echo "Running Pint..."
if [ -f "./vendor/bin/pint" ]; then
    ./vendor/bin/pint routes/api.php tests/Feature/Academics/AcademicStructureApiTest.php tests/Feature/Models/AcademicStructureModelsTest.php
else
    echo "Pint not found locally. Run: docker compose exec app ./vendor/bin/pint routes/api.php tests/Feature/Academics/AcademicStructureApiTest.php tests/Feature/Models/AcademicStructureModelsTest.php"
fi

echo "Academic routes and tests added."
echo ""
echo "Next commands:"
echo "docker compose exec app php artisan optimize:clear"
echo "docker compose exec app php artisan route:list"
echo "docker compose exec app php artisan test --filter=AcademicStructureModelsTest"
echo "docker compose exec app php artisan test --filter=AcademicStructureApiTest"
echo "docker compose exec app ./vendor/bin/pint --test"
