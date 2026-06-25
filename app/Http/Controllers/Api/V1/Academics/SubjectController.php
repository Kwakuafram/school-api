<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\Subjects\StoreSubjectRequest;
use App\Http\Requests\Academics\Subjects\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return SubjectResource::collection($this->academicCrudService->list(Subject::class, $request->only(['status', 'search', 'per_page'])));
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(Subject::class, $request->validated());

        return response()->json(['message' => 'Subject created successfully.', 'data' => new SubjectResource($record)], 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        return response()->json(['data' => new SubjectResource($subject)]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $record = $this->academicCrudService->update($subject, $request->validated());

        return response()->json(['message' => 'Subject updated successfully.', 'data' => new SubjectResource($record)]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $this->academicCrudService->delete($subject);

        return response()->json(['message' => 'Subject deleted successfully.']);
    }
}
