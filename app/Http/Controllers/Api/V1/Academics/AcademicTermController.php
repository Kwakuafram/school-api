<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\AcademicTerms\StoreAcademicTermRequest;
use App\Http\Requests\Academics\AcademicTerms\UpdateAcademicTermRequest;
use App\Http\Resources\AcademicTermResource;
use App\Models\AcademicTerm;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcademicTermController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AcademicTermResource::collection($this->academicCrudService->list(AcademicTerm::class, $request->only(['academic_year_id', 'status', 'search', 'per_page']), ['academicYear']));
    }

    public function store(StoreAcademicTermRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(AcademicTerm::class, $request->validated(), ['academicYear']);

        return response()->json(['message' => 'Academic term created successfully.', 'data' => new AcademicTermResource($record)], 201);
    }

    public function show(AcademicTerm $academicTerm): JsonResponse
    {
        return response()->json(['data' => new AcademicTermResource($academicTerm->load('academicYear'))]);
    }

    public function update(UpdateAcademicTermRequest $request, AcademicTerm $academicTerm): JsonResponse
    {
        $record = $this->academicCrudService->update($academicTerm, $request->validated(), ['academicYear']);

        return response()->json(['message' => 'Academic term updated successfully.', 'data' => new AcademicTermResource($record)]);
    }

    public function destroy(AcademicTerm $academicTerm): JsonResponse
    {
        $this->academicCrudService->delete($academicTerm);

        return response()->json(['message' => 'Academic term deleted successfully.']);
    }
}
