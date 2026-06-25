<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\ClassLevels\StoreClassLevelRequest;
use App\Http\Requests\Academics\ClassLevels\UpdateClassLevelRequest;
use App\Http\Resources\ClassLevelResource;
use App\Models\ClassLevel;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassLevelController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ClassLevelResource::collection($this->academicCrudService->list(ClassLevel::class, $request->only(['status', 'search', 'per_page']), ['arms']));
    }

    public function store(StoreClassLevelRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(ClassLevel::class, $request->validated(), ['arms']);

        return response()->json(['message' => 'Class level created successfully.', 'data' => new ClassLevelResource($record)], 201);
    }

    public function show(ClassLevel $classLevel): JsonResponse
    {
        return response()->json(['data' => new ClassLevelResource($classLevel->load('arms'))]);
    }

    public function update(UpdateClassLevelRequest $request, ClassLevel $classLevel): JsonResponse
    {
        $record = $this->academicCrudService->update($classLevel, $request->validated(), ['arms']);

        return response()->json(['message' => 'Class level updated successfully.', 'data' => new ClassLevelResource($record)]);
    }

    public function destroy(ClassLevel $classLevel): JsonResponse
    {
        $this->academicCrudService->delete($classLevel);

        return response()->json(['message' => 'Class level deleted successfully.']);
    }
}
