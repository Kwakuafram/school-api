<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\ClassArms\StoreClassArmRequest;
use App\Http\Requests\Academics\ClassArms\UpdateClassArmRequest;
use App\Http\Resources\ClassArmResource;
use App\Models\ClassArm;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassArmController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ClassArmResource::collection($this->academicCrudService->list(ClassArm::class, $request->only(['campus_id', 'class_level_id', 'status', 'search', 'per_page']), ['campus', 'classLevel']));
    }

    public function store(StoreClassArmRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(ClassArm::class, $request->validated(), ['campus', 'classLevel']);

        return response()->json(['message' => 'Class arm created successfully.', 'data' => new ClassArmResource($record)], 201);
    }

    public function show(ClassArm $classArm): JsonResponse
    {
        return response()->json(['data' => new ClassArmResource($classArm->load(['campus', 'classLevel']))]);
    }

    public function update(UpdateClassArmRequest $request, ClassArm $classArm): JsonResponse
    {
        $record = $this->academicCrudService->update($classArm, $request->validated(), ['campus', 'classLevel']);

        return response()->json(['message' => 'Class arm updated successfully.', 'data' => new ClassArmResource($record)]);
    }

    public function destroy(ClassArm $classArm): JsonResponse
    {
        $this->academicCrudService->delete($classArm);

        return response()->json(['message' => 'Class arm deleted successfully.']);
    }
}
