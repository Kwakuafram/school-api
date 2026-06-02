<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class UserController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $users = $this->userService->list($request->only([
            'search',
            'status',
            'per_page',
        ]));

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->create($request->validated());

        return response()->json([
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user->load(['roles', 'schools'])),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userService->update($user, $request->validated());

        return response()->json([
            'message' => 'User updated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->userService->delete($user);

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $user = $this->userService->activate($user);

        return response()->json([
            'message' => 'User activated successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function suspend(User $user): JsonResponse
    {
        $user = $this->userService->suspend($user);

        return response()->json([
            'message' => 'User suspended successfully.',
            'data' => new UserResource($user),
        ]);
    }
}