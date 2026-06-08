<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentEnrollmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'student_id' => $this->student_id,
            'academic_year' => $this->academic_year,
            'term' => $this->term,
            'class_level' => $this->class_level,
            'class_arm' => $this->class_arm,
            'enrolled_at' => $this->enrolled_at?->toDateString(),
            'exited_at' => $this->exited_at?->toDateString(),
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
        ];
    }
}