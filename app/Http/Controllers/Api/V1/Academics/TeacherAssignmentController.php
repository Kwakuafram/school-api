<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\TeacherAssignments\StoreTeacherAssignmentRequest;
use App\Http\Requests\Academics\TeacherAssignments\UpdateTeacherAssignmentRequest;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeacherAssignmentController extends Controller
{
    private const RELATIONS = [
        'campus',
        'academicYear',
        'academicTerm',
        'classLevel',
        'classArm',
        'subject',
        'teacher',
    ];

    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TeacherAssignmentResource::collection($this->academicCrudService->list(TeacherAssignment::class, $request->only([
            'academic_year_id',
            'academic_term_id',
            'campus_id',
            'class_level_id',
            'class_arm_id',
            'subject_id',
            'status',
            'per_page',
        ]), self::RELATIONS));
    }

    public function store(StoreTeacherAssignmentRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(TeacherAssignment::class, $request->validated(), self::RELATIONS);

        return response()->json(['message' => 'Teacher assignment created successfully.', 'data' => new TeacherAssignmentResource($record)], 201);
    }

    public function show(TeacherAssignment $teacherAssignment): JsonResponse
    {
        return response()->json(['data' => new TeacherAssignmentResource($teacherAssignment->load(self::RELATIONS))]);
    }

    public function update(UpdateTeacherAssignmentRequest $request, TeacherAssignment $teacherAssignment): JsonResponse
    {
        $record = $this->academicCrudService->update($teacherAssignment, $request->validated(), self::RELATIONS);

        return response()->json(['message' => 'Teacher assignment updated successfully.', 'data' => new TeacherAssignmentResource($record)]);
    }

    public function destroy(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->academicCrudService->delete($teacherAssignment);

        return response()->json(['message' => 'Teacher assignment deleted successfully.']);
    }
}
