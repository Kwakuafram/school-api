<?php

namespace App\Http\Controllers\Api\V1\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Resources\StudentAttendanceResource;
use App\Models\Student;
use App\Services\Attendance\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentAttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $service
    ) {}

    public function index(Request $request, Student $student): JsonResponse
    {
        $records = $this->service->listForStudent($student, $request->only([
            'status',
            'academic_year_id',
            'per_page',
        ]));

        return response()->json(StudentAttendanceResource::collection($records)->response()->getData(true));
    }
}
