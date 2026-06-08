<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Campuses\StoreCampusRequest;
use App\Http\Requests\Campuses\UpdateCampusRequest;
use App\Http\Resources\CampusResource;
use App\Models\Campus;
use App\Services\CampusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampusController extends Controller
{
    public function __construct(
        private readonly CampusService $campusService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $campuses = $this->campusService->list($request->only([
            'school_id',
            'search',
            'status',
            'per_page',
        ]));

        return CampusResource::collection($campuses);
    }

    public function store(StoreCampusRequest $request): JsonResponse
    {
        $campus = $this->campusService->create($request->validated());

        return response()->json([
            'message' => 'Campus created successfully.',
            'data' => new CampusResource($campus),
        ], 201);
    }

    public function show(Campus $campus): JsonResponse
    {
        return response()->json([
            'data' => new CampusResource($campus->load('school')),
        ]);
    }

    public function update(UpdateCampusRequest $request, Campus $campus): JsonResponse
    {
        $campus = $this->campusService->update($campus, $request->validated());

        return response()->json([
            'message' => 'Campus updated successfully.',
            'data' => new CampusResource($campus),
        ]);
    }

    public function destroy(Campus $campus): JsonResponse
    {
        $this->campusService->delete($campus);

        return response()->json([
            'message' => 'Campus deleted successfully.',
        ]);
    }
}
