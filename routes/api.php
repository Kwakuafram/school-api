<?php

use App\Http\Controllers\Api\V1\Academics\AcademicTermController;
use App\Http\Controllers\Api\V1\Academics\AcademicYearController;
use App\Http\Controllers\Api\V1\Academics\ClassArmController;
use App\Http\Controllers\Api\V1\Academics\ClassLevelController;
use App\Http\Controllers\Api\V1\Academics\SubjectController;
use App\Http\Controllers\Api\V1\Academics\TeacherAssignmentController;
use App\Http\Controllers\Api\V1\Attendance\AttendanceSessionController;
use App\Http\Controllers\Api\V1\Attendance\StudentAttendanceController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\GuardianController;
use App\Http\Controllers\Api\V1\SchoolController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toISOString(),
    ]));

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);

        /*
        |--------------------------------------------------------------------------
        | Platform-level routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('permission:schools.view')->get('schools', [SchoolController::class, 'index']);
        Route::middleware('permission:schools.create')->post('schools', [SchoolController::class, 'store']);
        Route::middleware('permission:schools.view')->get('schools/{school}', [SchoolController::class, 'show']);
        Route::middleware('permission:schools.update')->match(['put', 'patch'], 'schools/{school}', [SchoolController::class, 'update']);
        Route::middleware('permission:schools.delete')->delete('schools/{school}', [SchoolController::class, 'destroy']);

        /*
        |--------------------------------------------------------------------------
        | Tenant-level routes
        |--------------------------------------------------------------------------
        */
        Route::middleware('tenant')->group(function () {
            Route::middleware('permission:campuses.view')->get('campuses', [CampusController::class, 'index']);
            Route::middleware('permission:campuses.create')->post('campuses', [CampusController::class, 'store']);
            Route::middleware('permission:campuses.view')->get('campuses/{campus}', [CampusController::class, 'show']);
            Route::middleware('permission:campuses.update')->match(['put', 'patch'], 'campuses/{campus}', [CampusController::class, 'update']);
            Route::middleware('permission:campuses.delete')->delete('campuses/{campus}', [CampusController::class, 'destroy']);

            Route::middleware('permission:users.view')->get('users', [UserController::class, 'index']);
            Route::middleware('permission:users.create')->post('users', [UserController::class, 'store']);
            Route::middleware('permission:users.view')->get('users/{user}', [UserController::class, 'show']);
            Route::middleware('permission:users.update')->match(['put', 'patch'], 'users/{user}', [UserController::class, 'update']);
            Route::middleware('permission:users.delete')->delete('users/{user}', [UserController::class, 'destroy']);
            Route::middleware('permission:users.activate')->patch('users/{user}/activate', [UserController::class, 'activate']);
            Route::middleware('permission:users.suspend')->patch('users/{user}/suspend', [UserController::class, 'suspend']);

            Route::middleware('permission:audit_logs.view')->get('audit-logs', [AuditLogController::class, 'index']);
            Route::middleware('permission:audit_logs.view')->get('audit-logs/{auditLog}', [AuditLogController::class, 'show']);

            Route::middleware('permission:students.view')->get('students', [StudentController::class, 'index']);
            Route::middleware('permission:students.create')->post('students', [StudentController::class, 'store']);
            Route::middleware('permission:students.view')->get('students/{student}', [StudentController::class, 'show']);
            Route::middleware('permission:students.update')->match(['put', 'patch'], 'students/{student}', [StudentController::class, 'update']);
            Route::middleware('permission:students.delete')->delete('students/{student}', [StudentController::class, 'destroy']);

            Route::middleware('permission:guardians.view')->get('guardians', [GuardianController::class, 'index']);
            Route::middleware('permission:guardians.create')->post('guardians', [GuardianController::class, 'store']);
            Route::middleware('permission:guardians.view')->get('guardians/{guardian}', [GuardianController::class, 'show']);
            Route::middleware('permission:guardians.update')->match(['put', 'patch'], 'guardians/{guardian}', [GuardianController::class, 'update']);
            Route::middleware('permission:guardians.delete')->delete('guardians/{guardian}', [GuardianController::class, 'destroy']);

            Route::middleware('permission:students.guardians.manage')->post('students/{student}/guardians', [GuardianController::class, 'attachToStudent']);
            Route::middleware('permission:students.guardians.manage')->patch('students/{student}/guardians/{guardian}', [GuardianController::class, 'updateStudentGuardian']);
            Route::middleware('permission:students.guardians.manage')->delete('students/{student}/guardians/{guardian}', [GuardianController::class, 'detachFromStudent']);

            /*
|--------------------------------------------------------------------------
| Academic Structure Routes
|--------------------------------------------------------------------------
*/

            Route::middleware('permission:academics.view')
                ->get('academic-years', [AcademicYearController::class, 'index']);
            Route::middleware('permission:academics.create')
                ->post('academic-years', [AcademicYearController::class, 'store']);
            Route::middleware('permission:academics.view')
                ->get('academic-years/{academicYear}', [AcademicYearController::class, 'show']);
            Route::middleware('permission:academics.update')
                ->match(['put', 'patch'], 'academic-years/{academicYear}', [AcademicYearController::class, 'update']);
            Route::middleware('permission:academics.delete')
                ->delete('academic-years/{academicYear}', [AcademicYearController::class, 'destroy']);

            Route::middleware('permission:academics.view')
                ->get('academic-terms', [AcademicTermController::class, 'index']);
            Route::middleware('permission:academics.create')
                ->post('academic-terms', [AcademicTermController::class, 'store']);
            Route::middleware('permission:academics.view')
                ->get('academic-terms/{academicTerm}', [AcademicTermController::class, 'show']);
            Route::middleware('permission:academics.update')
                ->match(['put', 'patch'], 'academic-terms/{academicTerm}', [AcademicTermController::class, 'update']);
            Route::middleware('permission:academics.delete')
                ->delete('academic-terms/{academicTerm}', [AcademicTermController::class, 'destroy']);

            Route::middleware('permission:academics.view')
                ->get('class-levels', [ClassLevelController::class, 'index']);
            Route::middleware('permission:academics.create')
                ->post('class-levels', [ClassLevelController::class, 'store']);
            Route::middleware('permission:academics.view')
                ->get('class-levels/{classLevel}', [ClassLevelController::class, 'show']);
            Route::middleware('permission:academics.update')
                ->match(['put', 'patch'], 'class-levels/{classLevel}', [ClassLevelController::class, 'update']);
            Route::middleware('permission:academics.delete')
                ->delete('class-levels/{classLevel}', [ClassLevelController::class, 'destroy']);

            Route::middleware('permission:academics.view')
                ->get('class-arms', [ClassArmController::class, 'index']);
            Route::middleware('permission:academics.create')
                ->post('class-arms', [ClassArmController::class, 'store']);
            Route::middleware('permission:academics.view')
                ->get('class-arms/{classArm}', [ClassArmController::class, 'show']);
            Route::middleware('permission:academics.update')
                ->match(['put', 'patch'], 'class-arms/{classArm}', [ClassArmController::class, 'update']);
            Route::middleware('permission:academics.delete')
                ->delete('class-arms/{classArm}', [ClassArmController::class, 'destroy']);

            Route::middleware('permission:academics.view')
                ->get('subjects', [SubjectController::class, 'index']);
            Route::middleware('permission:academics.create')
                ->post('subjects', [SubjectController::class, 'store']);
            Route::middleware('permission:academics.view')
                ->get('subjects/{subject}', [SubjectController::class, 'show']);
            Route::middleware('permission:academics.update')
                ->match(['put', 'patch'], 'subjects/{subject}', [SubjectController::class, 'update']);
            Route::middleware('permission:academics.delete')
                ->delete('subjects/{subject}', [SubjectController::class, 'destroy']);

            Route::middleware('permission:teacher_assignments.view')
                ->get('teacher-assignments', [TeacherAssignmentController::class, 'index']);
            Route::middleware('permission:teacher_assignments.create')
                ->post('teacher-assignments', [TeacherAssignmentController::class, 'store']);
            Route::middleware('permission:teacher_assignments.view')
                ->get('teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'show']);
            Route::middleware('permission:teacher_assignments.update')
                ->match(['put', 'patch'], 'teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'update']);
            Route::middleware('permission:teacher_assignments.delete')
                ->delete('teacher-assignments/{teacherAssignment}', [TeacherAssignmentController::class, 'destroy']);

            /*
|--------------------------------------------------------------------------
| Attendance Routes
|--------------------------------------------------------------------------
*/
            Route::middleware('permission:attendance.view')
                ->get('attendance/sessions', [AttendanceSessionController::class, 'index']);
            Route::middleware('permission:attendance.take')
                ->post('attendance/sessions', [AttendanceSessionController::class, 'store']);
            Route::middleware('permission:attendance.view')
                ->get('attendance/sessions/{session}', [AttendanceSessionController::class, 'show']);
            Route::middleware('permission:attendance.take')
                ->patch('attendance/sessions/{session}/submit', [AttendanceSessionController::class, 'submit']);
            Route::middleware('permission:attendance.approve')
                ->patch('attendance/sessions/{session}/approve', [AttendanceSessionController::class, 'approve']);
            Route::middleware('permission:attendance.take')
                ->delete('attendance/sessions/{session}', [AttendanceSessionController::class, 'destroy']);

            Route::middleware('permission:attendance.view')
                ->get('students/{student}/attendance', [StudentAttendanceController::class, 'index']);
        });
    });
});
