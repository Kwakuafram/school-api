<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentResource extends JsonResource
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
            'subject_id' => $this->subject_id,
            'teacher_user_id' => $this->teacher_user_id,
            'assignment_type' => $this->assignment_type,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'academic_term' => new AcademicTermResource($this->whenLoaded('academicTerm')),
            'class_level' => new ClassLevelResource($this->whenLoaded('classLevel')),
            'class_arm' => new ClassArmResource($this->whenLoaded('classArm')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
        ];
    }
}
