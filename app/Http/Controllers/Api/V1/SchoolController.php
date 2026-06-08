<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schools\StoreSchoolRequest;
use App\Http\Requests\Schools\UpdateSchoolRequest;
use App\Http\Resources\SchoolResource;
use App\Models\School;
use App\Services\SchoolService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SchoolController extends Controller
{
    public function __construct(
        private readonly SchoolService $schoolService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $schools = $this->schoolService->list($request->only([
            'search',
            'status',
            'per_page',
        ]));

        return SchoolResource::collection($schools);
    }

    public function store(StoreSchoolRequest $request): JsonResponse
    {
        $school = $this->schoolService->create($request->validated());

        return response()->json([
            'message' => 'School created successfully.',
            'data' => new SchoolResource($school),
        ], 201);
    }

    public function show(School $school): JsonResponse
    {
        return response()->json([
            'data' => new SchoolResource($school->load('campuses')),
        ]);
    }

    public function update(UpdateSchoolRequest $request, School $school): JsonResponse
    {
        $school = $this->schoolService->update($school, $request->validated());

        return response()->json([
            'message' => 'School updated successfully.',
            'data' => new SchoolResource($school),
        ]);
    }

    public function destroy(School $school): JsonResponse
    {
        $this->schoolService->delete($school);

        return response()->json([
            'message' => 'School deleted successfully.',
        ]);
    }
}
