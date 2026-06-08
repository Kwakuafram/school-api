<?php

namespace App\Http\Requests\Guardians;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'alternate_phone' => ['sometimes', 'nullable', 'string', 'max:50'],

            'occupation' => ['sometimes', 'nullable', 'string', 'max:255'],
            'employer' => ['sometimes', 'nullable', 'string', 'max:255'],

            'address' => ['sometimes', 'nullable', 'string'],

            'status' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['active', 'inactive']),
            ],

            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
