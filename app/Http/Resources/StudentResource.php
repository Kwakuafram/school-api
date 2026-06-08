<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'admission_number' => $this->admission_number,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'preferred_name' => $this->preferred_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'email' => $this->email,
            'phone' => $this->phone,
            'nationality' => $this->nationality,
            'religion' => $this->religion,
            'blood_group' => $this->blood_group,
            'address' => $this->address,
            'photo_path' => $this->photo_path,
            'admission_date' => $this->admission_date?->toDateString(),
            'status' => $this->status,
            'medical_info' => $this->medical_info,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'guardians' => GuardianResource::collection($this->whenLoaded('guardians')),
            'current_enrollment' => new StudentEnrollmentResource($this->whenLoaded('currentEnrollment')),
            'enrollments' => StudentEnrollmentResource::collection($this->whenLoaded('enrollments')),
        ];
    }
}
