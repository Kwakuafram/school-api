<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CampusController;
use App\Http\Controllers\Api\V1\SchoolController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'time' => now()->toISOString(),
    ]));

    /*
    |--------------------------------------------------------------------------
    | Public auth routes
    |--------------------------------------------------------------------------
    */
    Route::post('auth/login', [AuthController::class, 'login']);

    /*
    |--------------------------------------------------------------------------
    | Protected routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/logout-all', [AuthController::class, 'logoutAll']);

        Route::apiResource('schools', SchoolController::class);
        Route::apiResource('campuses', CampusController::class);
        Route::apiResource('users', UserController::class);

        Route::patch('users/{user}/activate', [UserController::class, 'activate']);
        Route::patch('users/{user}/suspend', [UserController::class, 'suspend']);
    });
});