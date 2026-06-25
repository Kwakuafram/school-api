<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\AcademicYears\StoreAcademicYearRequest;
use App\Http\Requests\Academics\AcademicYears\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcademicYearController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AcademicYearResource::collection($this->academicCrudService->list(AcademicYear::class, $request->only(['status', 'search', 'per_page']), ['terms']));
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(AcademicYear::class, $request->validated(), ['terms']);

        return response()->json(['message' => 'Academic year created successfully.', 'data' => new AcademicYearResource($record)], 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json(['data' => new AcademicYearResource($academicYear->load('terms'))]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $record = $this->academicCrudService->update($academicYear, $request->validated(), ['terms']);

        return response()->json(['message' => 'Academic year updated successfully.', 'data' => new AcademicYearResource($record)]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $this->academicCrudService->delete($academicYear);

        return response()->json(['message' => 'Academic year deleted successfully.']);
    }
}
