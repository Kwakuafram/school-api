<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'status' => $this->status,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'roles' => $this->whenLoaded('roles', function () {
                return $this->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ]);
            }),

            'schools' => $this->whenLoaded('schools', function () {
                return $this->schools->map(fn ($school) => [
                    'id' => $school->id,
                    'name' => $school->name,
                    'code' => $school->code,
                    'role_context' => $school->pivot?->role_context,
                    'is_default' => (bool) $school->pivot?->is_default,
                ]);
            }),
        ];
    }
}   