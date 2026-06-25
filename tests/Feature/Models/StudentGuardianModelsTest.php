<?php

namespace Tests\Feature\Models;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\Guardian;
use App\Models\School;
use App\Models\Student;
use App\Models\StudentEnrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentGuardianModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_model_can_be_created_with_uuid_casts_and_relationships(): void
    {
        $school = $this->createSchool();
        $campus = $this->createCampus($school);

        $student = Student::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'admission_number' => 'ADM-001',
            'first_name' => 'Kwame',
            'middle_name' => 'Kojo',
            'last_name' => 'Mensah',
            'preferred_name' => 'Kwame',
            'gender' => 'male',
            'date_of_birth' => '2015-05-12',
            'email' => 'kwame.mensah@example.test',
            'phone' => '+233000000111',
            'nationality' => 'Ghanaian',
            'religion' => 'Christian',
            'blood_group' => 'O+',
            'address' => 'Kumasi',
            'admission_date' => '2026-06-01',
            'status' => 'active',
            'medical_info' => [
                'allergies' => ['peanuts'],
            ],
            'metadata' => [
                'source' => 'phpunit',
            ],
        ]);

        $this->assertNotNull($student->id);
        $this->assertIsString($student->id);
        $this->assertSame($school->id, $student->school->id);
        $this->assertSame($campus->id, $student->campus->id);
        $this->assertSame('Kwame Kojo Mensah', $student->full_name);
        $this->assertSame('2015-05-12', $student->date_of_birth->toDateString());
        $this->assertSame('2026-06-01', $student->admission_date->toDateString());
        $this->assertSame(['peanuts'], $student->medical_info['allergies']);
        $this->assertSame('phpunit', $student->metadata['source']);
    }

    public function test_guardian_model_can_be_created_and_attached_to_student(): void
    {
        $school = $this->createSchool();
        $campus = $this->createCampus($school);
        $student = $this->createStudent($school, $campus);

        $guardian = Guardian::query()->create([
            'school_id' => $school->id,
            'first_name' => 'Ama',
            'middle_name' => 'Akua',
            'last_name' => 'Mensah',
            'email' => 'ama.mensah@example.test',
            'phone' => '+233000000333',
            'alternate_phone' => '+233000000334',
            'occupation' => 'Teacher',
            'employer' => 'Kumasi Basic School',
            'address' => 'Kumasi',
            'status' => 'active',
            'metadata' => [
                'preferred_contact_method' => 'sms',
            ],
        ]);

        $student->guardians()->attach($guardian->id, [
            'relationship' => 'mother',
            'is_primary' => true,
            'can_pick_up' => true,
            'receives_sms' => true,
            'receives_email' => false,
        ]);

        $student->refresh()->load('guardians');
        $attachedGuardian = $student->guardians->first();

        $this->assertNotNull($guardian->id);
        $this->assertIsString($guardian->id);
        $this->assertSame('Ama Akua Mensah', $guardian->full_name);
        $this->assertCount(1, $student->guardians);
        $this->assertSame($guardian->id, $attachedGuardian->id);
        $this->assertSame('mother', $attachedGuardian->pivot->relationship);
        $this->assertTrue((bool) $attachedGuardian->pivot->is_primary);
        $this->assertTrue((bool) $attachedGuardian->pivot->can_pick_up);
        $this->assertTrue((bool) $attachedGuardian->pivot->receives_sms);
        $this->assertFalse((bool) $attachedGuardian->pivot->receives_email);
    }

    public function test_student_enrollment_belongs_to_student_school_and_campus(): void
    {
        $school = $this->createSchool();
        $campus = $this->createCampus($school);
        $student = $this->createStudent($school, $campus);

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

        $classLevel = ClassLevel::query()->create([
            'school_id' => $school->id,
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $classArm = ClassArm::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'class_level_id' => $classLevel->id,
            'name' => 'Basic 1 A',
            'code' => 'A',
            'status' => 'active',
        ]);

        $enrollment = StudentEnrollment::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'academic_year_id' => $academicYear->id,
            'academic_term_id' => $term->id,
            'class_level_id' => $classLevel->id,
            'class_arm_id' => $classArm->id,
            'enrolled_at' => '2026-06-01',
            'status' => 'active',
            'metadata' => ['source' => 'phpunit'],
        ]);

        $student->update(['current_enrollment_id' => $enrollment->id]);
        $student->refresh()->load('currentEnrollment', 'enrollments');

        $this->assertNotNull($enrollment->id);
        $this->assertSame($student->id, $enrollment->student->id);
        $this->assertSame($campus->id, $enrollment->campus->id);
        $this->assertSame('2026-06-01', $enrollment->enrolled_at->toDateString());
        $this->assertSame($classLevel->id, $student->currentEnrollment->class_level_id);
        $this->assertSame($classArm->id, $student->currentEnrollment->class_arm_id);
        $this->assertCount(1, $student->enrollments);
    }

    public function test_current_enrollment_uses_direct_pointer_without_uuid_aggregate(): void
    {
        $school = $this->createSchool();
        $campus = $this->createCampus($school);
        $student = $this->createStudent($school, $campus);

        $year2025 = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2025/2026',
            'start_date' => '2025-09-01',
            'end_date' => '2026-07-31',
            'status' => 'active',
        ]);

        $year2026 = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $classLevel = ClassLevel::query()->create([
            'school_id' => $school->id,
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        StudentEnrollment::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'academic_year_id' => $year2025->id,
            'class_level_id' => $classLevel->id,
            'enrolled_at' => '2025-09-01',
            'status' => 'active',
        ]);

        $newEnrollment = StudentEnrollment::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'student_id' => $student->id,
            'academic_year_id' => $year2026->id,
            'class_level_id' => $classLevel->id,
            'enrolled_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $student->update(['current_enrollment_id' => $newEnrollment->id]);
        $student->refresh()->load('currentEnrollment');

        $this->assertSame($year2026->id, $student->currentEnrollment->academic_year_id);
        $this->assertSame($newEnrollment->id, $student->current_enrollment_id);
    }

    private function createSchool(array $overrides = []): School
    {
        return School::query()->create(array_merge([
            'name' => 'Demo International School',
            'code' => 'DIS'.uniqid(),
            'email' => 'info'.uniqid().'@demo-school.test',
            'status' => 'active',
        ], $overrides));
    }

    private function createCampus(School $school, array $overrides = []): Campus
    {
        return Campus::query()->create(array_merge([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN'.uniqid(),
            'is_main' => true,
            'status' => 'active',
        ], $overrides));
    }

    private function createStudent(School $school, Campus $campus, array $overrides = []): Student
    {
        return Student::query()->create(array_merge([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'admission_number' => 'ADM-'.uniqid(),
            'first_name' => 'Kwame',
            'last_name' => 'Mensah',
            'status' => 'active',
        ], $overrides));
    }
}
