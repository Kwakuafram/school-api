<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentAttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'attendance_session_id' => $this->attendance_session_id,
            'student_id' => $this->student_id,
            'status' => $this->status,
            'notes' => $this->notes,
            'recorded_by' => $this->recorded_by,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'student' => new StudentResource($this->whenLoaded('student')),
            'session' => new AttendanceSessionResource($this->whenLoaded('session')),
        ];
    }
}
