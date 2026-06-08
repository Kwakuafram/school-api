<?php

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
        });
    });
});
