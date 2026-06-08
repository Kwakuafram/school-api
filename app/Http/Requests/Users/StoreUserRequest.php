<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;

        // Later:
        // return $this->user()?->can('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'status' => [
                'nullable',
                'string',
                Rule::in(['active', 'inactive', 'suspended']),
            ],

            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],

            'school_id' => ['nullable', 'uuid', Rule::exists('schools', 'id')],
            'role_context' => ['nullable', 'string', 'max:50'],
            'is_default_school' => ['nullable', 'boolean'],
        ];
    }
}
