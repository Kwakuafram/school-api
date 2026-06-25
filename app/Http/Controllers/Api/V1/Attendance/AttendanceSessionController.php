<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceSessionRequest;
use App\Http\Requests\Attendance\SubmitAttendanceRequest;
use App\Http\Resources\AttendanceSessionResource;
use App\Models\AttendanceSession;
use App\Services\Attendance\AttendanceService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceSessionController extends Controller
{
    public function __construct(
        private readonly AttendanceService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = $this->service->list($request->only([
            'class_arm_id',
            'class_level_id',
            'academic_year_id',
            'academic_term_id',
            'status',
            'session_date',
            'per_page',
        ]));

        return response()->json(AttendanceSessionResource::collection($sessions)->response()->getData(true));
    }

    public function store(StoreAttendanceSessionRequest $request): JsonResponse
    {
        $session = $this->service->create($request->validated());

        return response()->json(['data' => new AttendanceSessionResource($session)], 201);
    }

    public function show(AttendanceSession $session): JsonResponse
    {
        abort_unless(
            $session->school_id === app(TenantContext::class)->requireSchoolId(),
            403
        );

        $session->load(['classLevel', 'classArm', 'academicYear', 'academicTerm', 'studentAttendances.student']);

        return response()->json(['data' => new AttendanceSessionResource($session)]);
    }

    public function submit(SubmitAttendanceRequest $request, AttendanceSession $session): JsonResponse
    {
        $session = $this->service->submit($session, $request->validated()['attendances']);

        return response()->json(['data' => new AttendanceSessionResource($session)]);
    }

    public function approve(AttendanceSession $session): JsonResponse
    {
        $session = $this->service->approve($session);

        return response()->json(['data' => new AttendanceSessionResource($session)]);
    }

    public function destroy(AttendanceSession $session): JsonResponse
    {
        $this->service->delete($session);

        return response()->json(['message' => 'Attendance session deleted.']);
    }
}
