<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'academic_year_id' => $this->academic_year_id,
            'academic_term_id' => $this->academic_term_id,
            'class_level_id' => $this->class_level_id,
            'class_arm_id' => $this->class_arm_id,
            'session_date' => $this->session_date?->toDateString(),
            'period' => $this->period,
            'status' => $this->status,
            'submitted_by' => $this->submitted_by,
            'approved_by' => $this->approved_by,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'approved_at' => $this->approved_at?->toISOString(),
            'notes' => $this->notes,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'academic_term' => new AcademicTermResource($this->whenLoaded('academicTerm')),
            'class_level' => new ClassLevelResource($this->whenLoaded('classLevel')),
            'class_arm' => new ClassArmResource($this->whenLoaded('classArm')),
            'student_attendances' => StudentAttendanceResource::collection($this->whenLoaded('studentAttendances')),
        ];
    }
}
