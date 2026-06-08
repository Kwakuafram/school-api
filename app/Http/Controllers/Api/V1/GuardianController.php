<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardians\StoreGuardianRequest;
use App\Http\Requests\Guardians\UpdateGuardianRequest;
use App\Http\Requests\Students\AttachGuardianRequest;
use App\Http\Requests\Students\UpdateStudentGuardianRequest;
use App\Http\Resources\GuardianResource;
use App\Http\Resources\StudentResource;
use App\Models\Guardian;
use App\Models\Student;
use App\Services\Students\GuardianService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class GuardianController extends Controller
{
    public function __construct(
        private readonly GuardianService $guardianService,
        private readonly TenantContext $tenantContext
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $guardians = $this->guardianService->list($request->only([
            'status',
            'search',
            'per_page',
        ]));

        return GuardianResource::collection($guardians);
    }

    public function store(StoreGuardianRequest $request): JsonResponse
    {
        $guardian = $this->guardianService->create($request->validated());

        return response()->json([
            'message' => 'Guardian created successfully.',
            'data' => new GuardianResource($guardian),
        ], 201);
    }

    public function show(Guardian $guardian): JsonResponse
    {
        abort_unless(
            $guardian->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot view a guardian outside the current school.'
        );

        return response()->json([
            'data' => new GuardianResource($guardian->load('students')),
        ]);
    }

    public function update(UpdateGuardianRequest $request, Guardian $guardian): JsonResponse
    {
        $guardian = $this->guardianService->update($guardian, $request->validated());

        return response()->json([
            'message' => 'Guardian updated successfully.',
            'data' => new GuardianResource($guardian),
        ]);
    }

    public function destroy(Guardian $guardian): JsonResponse
    {
        $this->guardianService->delete($guardian);

        return response()->json([
            'message' => 'Guardian deleted successfully.',
        ]);
    }

    public function attachToStudent(AttachGuardianRequest $request, Student $student): JsonResponse
    {
        $student = $this->guardianService->attachToStudent($student, $request->validated());

        return response()->json([
            'message' => 'Guardian attached to student successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function updateStudentGuardian(
        UpdateStudentGuardianRequest $request,
        Student $student,
        Guardian $guardian
    ): JsonResponse {
        $student = $this->guardianService->updateStudentGuardian(
            $student,
            $guardian,
            $request->validated()
        );

        return response()->json([
            'message' => 'Student guardian relationship updated successfully.',
            'data' => new StudentResource($student),
        ]);
    }

    public function detachFromStudent(Student $student, Guardian $guardian): JsonResponse
    {
        $student = $this->guardianService->detachFromStudent($student, $guardian);

        return response()->json([
            'message' => 'Guardian detached from student successfully.',
            'data' => new StudentResource($student),
        ]);
    }
}
