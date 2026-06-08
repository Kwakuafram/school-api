<?php

namespace App\Http\Middleware;

use App\Models\Campus;
use App\Models\School;
use App\Services\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $this->tenantContext->clear();

        $user = $request->user();

        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        $schoolId = $request->header('X-School-ID');
        $campusId = $request->header('X-Campus-ID');

        if (! $schoolId) {
            return response()->json([
                'message' => 'Missing tenant context.',
                'errors' => [
                    'X-School-ID' => [
                        'The X-School-ID header is required.',
                    ],
                ],
            ], 422);
        }

        $school = School::query()
            ->whereKey($schoolId)
            ->where('status', 'active')
            ->first();

        if (! $school) {
            return response()->json([
                'message' => 'Invalid tenant context.',
                'errors' => [
                    'X-School-ID' => [
                        'The selected school does not exist or is inactive.',
                    ],
                ],
            ], 422);
        }

        if (! $this->tenantContext->userBelongsToSchool($user, $school->id)) {
            return response()->json([
                'message' => 'You do not have access to this school.',
            ], 403);
        }

        $this->tenantContext->setSchool($school);

        if ($campusId) {
            $campus = Campus::query()
                ->whereKey($campusId)
                ->where('school_id', $school->id)
                ->where('status', 'active')
                ->first();

            if (! $campus) {
                return response()->json([
                    'message' => 'Invalid campus context.',
                    'errors' => [
                        'X-Campus-ID' => [
                            'The selected campus does not exist, is inactive, or does not belong to the selected school.',
                        ],
                    ],
                ], 422);
            }

            $this->tenantContext->setCampus($campus);
        }

        return $next($request);
    }
}
