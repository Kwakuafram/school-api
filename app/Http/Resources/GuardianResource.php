<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GuardianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'alternate_phone' => $this->alternate_phone,
            'occupation' => $this->occupation,
            'employer' => $this->employer,
            'address' => $this->address,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'students' => StudentResource::collection($this->whenLoaded('students')),

            'pivot' => $this->whenPivotLoaded('student_guardian', function () {
                return [
                    'relationship' => $this->pivot->relationship,
                    'is_primary' => (bool) $this->pivot->is_primary,
                    'can_pick_up' => (bool) $this->pivot->can_pick_up,
                    'receives_sms' => (bool) $this->pivot->receives_sms,
                    'receives_email' => (bool) $this->pivot->receives_email,
                ];
            }),
        ];
    }
}