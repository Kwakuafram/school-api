<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicTermResource;
use App\Http\Resources\AcademicYearResource;
use App\Http\Resources\ClassArmResource;
use App\Http\Resources\ClassLevelResource;
use App\Http\Resources\SubjectResource;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Services\Academics\AcademicCrudService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AcademicStructureController extends Controller
{
    public function __construct(
        private readonly AcademicCrudService $academicCrudService
    ) {}

    public function academicYears(Request $request): AnonymousResourceCollection
    {
        return AcademicYearResource::collection(
            $this->academicCrudService->list(AcademicYear::class, $request->only(['status', 'search', 'per_page']), ['terms'])
        );
    }

    public function storeAcademicYear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(AcademicYear::class, $data, ['terms']);

        return response()->json([
            'message' => 'Academic year created successfully.',
            'data' => new AcademicYearResource($record),
        ], 201);
    }

    public function showAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        return response()->json([
            'data' => new AcademicYearResource($academicYear->load('terms')),
        ]);
    }

    public function updateAcademicYear(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($academicYear, $data, ['terms']);

        return response()->json([
            'message' => 'Academic year updated successfully.',
            'data' => new AcademicYearResource($record),
        ]);
    }

    public function destroyAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $this->academicCrudService->delete($academicYear);

        return response()->json(['message' => 'Academic year deleted successfully.']);
    }

    public function academicTerms(Request $request): AnonymousResourceCollection
    {
        return AcademicTermResource::collection(
            $this->academicCrudService->list(AcademicTerm::class, $request->only(['academic_year_id', 'status', 'search', 'per_page']), ['academicYear'])
        );
    }

    public function storeAcademicTerm(Request $request): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(AcademicTerm::class, $data, ['academicYear']);

        return response()->json([
            'message' => 'Academic term created successfully.',
            'data' => new AcademicTermResource($record),
        ], 201);
    }

    public function showAcademicTerm(AcademicTerm $academicTerm): JsonResponse
    {
        return response()->json(['data' => new AcademicTermResource($academicTerm->load('academicYear'))]);
    }

    public function updateAcademicTerm(Request $request, AcademicTerm $academicTerm): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($academicTerm, $data, ['academicYear']);

        return response()->json([
            'message' => 'Academic term updated successfully.',
            'data' => new AcademicTermResource($record),
        ]);
    }

    public function destroyAcademicTerm(AcademicTerm $academicTerm): JsonResponse
    {
        $this->academicCrudService->delete($academicTerm);

        return response()->json(['message' => 'Academic term deleted successfully.']);
    }

    public function classLevels(Request $request): AnonymousResourceCollection
    {
        return ClassLevelResource::collection(
            $this->academicCrudService->list(ClassLevel::class, $request->only(['status', 'search', 'per_page']), ['arms'])
        );
    }

    public function storeClassLevel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(ClassLevel::class, $data, ['arms']);

        return response()->json([
            'message' => 'Class level created successfully.',
            'data' => new ClassLevelResource($record),
        ], 201);
    }

    public function showClassLevel(ClassLevel $classLevel): JsonResponse
    {
        return response()->json(['data' => new ClassLevelResource($classLevel->load('arms'))]);
    }

    public function updateClassLevel(Request $request, ClassLevel $classLevel): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($classLevel, $data, ['arms']);

        return response()->json([
            'message' => 'Class level updated successfully.',
            'data' => new ClassLevelResource($record),
        ]);
    }

    public function destroyClassLevel(ClassLevel $classLevel): JsonResponse
    {
        $this->academicCrudService->delete($classLevel);

        return response()->json(['message' => 'Class level deleted successfully.']);
    }

    public function classArms(Request $request): AnonymousResourceCollection
    {
        return ClassArmResource::collection(
            $this->academicCrudService->list(ClassArm::class, $request->only(['campus_id', 'class_level_id', 'status', 'search', 'per_page']), ['campus', 'classLevel'])
        );
    }

    public function storeClassArm(Request $request): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(ClassArm::class, $data, ['campus', 'classLevel']);

        return response()->json([
            'message' => 'Class arm created successfully.',
            'data' => new ClassArmResource($record),
        ], 201);
    }

    public function showClassArm(ClassArm $classArm): JsonResponse
    {
        return response()->json(['data' => new ClassArmResource($classArm->load(['campus', 'classLevel']))]);
    }

    public function updateClassArm(Request $request, ClassArm $classArm): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($classArm, $data, ['campus', 'classLevel']);

        return response()->json([
            'message' => 'Class arm updated successfully.',
            'data' => new ClassArmResource($record),
        ]);
    }

    public function destroyClassArm(ClassArm $classArm): JsonResponse
    {
        $this->academicCrudService->delete($classArm);

        return response()->json(['message' => 'Class arm deleted successfully.']);
    }

    public function subjects(Request $request): AnonymousResourceCollection
    {
        return SubjectResource::collection(
            $this->academicCrudService->list(Subject::class, $request->only(['status', 'search', 'per_page']))
        );
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(Subject::class, $data);

        return response()->json([
            'message' => 'Subject created successfully.',
            'data' => new SubjectResource($record),
        ], 201);
    }

    public function showSubject(Subject $subject): JsonResponse
    {
        return response()->json(['data' => new SubjectResource($subject)]);
    }

    public function updateSubject(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($subject, $data);

        return response()->json([
            'message' => 'Subject updated successfully.',
            'data' => new SubjectResource($record),
        ]);
    }

    public function destroySubject(Subject $subject): JsonResponse
    {
        $this->academicCrudService->delete($subject);

        return response()->json(['message' => 'Subject deleted successfully.']);
    }

    public function teacherAssignments(Request $request): AnonymousResourceCollection
    {
        return TeacherAssignmentResource::collection(
            $this->academicCrudService->list(TeacherAssignment::class, $request->only([
                'academic_year_id',
                'academic_term_id',
                'campus_id',
                'class_level_id',
                'class_arm_id',
                'subject_id',
                'status',
                'per_page',
            ]), ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher'])
        );
    }

    public function storeTeacherAssignment(Request $request): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'academic_term_id' => ['nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'class_arm_id' => ['nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $schoolId)],
            'assignment_type' => ['nullable', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(TeacherAssignment::class, $data, ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher']);

        return response()->json([
            'message' => 'Teacher assignment created successfully.',
            'data' => new TeacherAssignmentResource($record),
        ], 201);
    }

    public function showTeacherAssignment(TeacherAssignment $teacherAssignment): JsonResponse
    {
        return response()->json([
            'data' => new TeacherAssignmentResource(
                $teacherAssignment->load(['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher'])
            ),
        ]);
    }

    public function updateTeacherAssignment(Request $request, TeacherAssignment $teacherAssignment): JsonResponse
    {
        $schoolId = app(TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'academic_term_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'class_arm_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['sometimes', 'required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $schoolId)],
            'assignment_type' => ['sometimes', 'required', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($teacherAssignment, $data, ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher']);

        return response()->json([
            'message' => 'Teacher assignment updated successfully.',
            'data' => new TeacherAssignmentResource($record),
        ]);
    }

    public function destroyTeacherAssignment(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->academicCrudService->delete($teacherAssignment);

        return response()->json(['message' => 'Teacher assignment deleted successfully.']);
    }
}
