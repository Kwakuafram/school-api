<?php

namespace App\Http\Requests\Schools;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        // Later:
        // return $this->user()?->can('schools.update') ?? false;
    }

    public function rules(): array
    {
        $schoolId = $this->route('school')?->id ?? $this->route('school');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('schools', 'code')->ignore($schoolId),
            ],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'website' => ['sometimes', 'nullable', 'url', 'max:255'],

            'country' => ['sometimes', 'nullable', 'string', 'max:100'],
            'region' => ['sometimes', 'nullable', 'string', 'max:100'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'address' => ['sometimes', 'nullable', 'string'],

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
