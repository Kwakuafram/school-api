<?php

namespace App\Http\Requests\Campuses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        // Later:
        // return $this->user()?->can('campuses.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'school_id' => ['required', 'uuid', Rule::exists('schools', 'id')],

            'name' => ['required', 'string', 'max:255'],

            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('campuses', 'code')
                    ->where('school_id', $this->input('school_id')),
            ],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string'],

            'is_main' => ['nullable', 'boolean'],

            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],

            'settings' => ['nullable', 'array'],
        ];
    }
}