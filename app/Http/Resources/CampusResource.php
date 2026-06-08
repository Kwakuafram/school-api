<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CampusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'email' => $this->email,
            'phone' => $this->phone,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
            'address' => $this->address,
            'is_main' => (bool) $this->is_main,
            'status' => $this->status,
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'school' => new SchoolResource($this->whenLoaded('school')),
        ];
    }
}
