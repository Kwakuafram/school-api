<?php

namespace App\Http\Requests\Campuses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        // Later:
        // return $this->user()?->can('campuses.update') ?? false;
    }

    public function rules(): array
    {
        $campus = $this->route('campus');
        $campusId = $campus?->id ?? $campus;
        $schoolId = $this->input('school_id', $campus?->school_id);

        return [
            'school_id' => ['sometimes', 'required', 'uuid', Rule::exists('schools', 'id')],

            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('campuses', 'code')
                    ->where('school_id', $schoolId)
                    ->ignore($campusId),
            ],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],

            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'region' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string'],

            'is_main' => ['sometimes', 'boolean'],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],

            'settings' => ['sometimes', 'nullable', 'array'],
        ];
    }
}