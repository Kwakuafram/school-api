<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassArmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'class_level_id' => $this->class_level_id,
            'name' => $this->name,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'class_level' => new ClassLevelResource($this->whenLoaded('classLevel')),
        ];
    }
}
