<?php

namespace App\Http\Requests\Campuses;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $campus = $this->route('campus');

        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            'code' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('campuses', 'code')
                    ->where('school_id', $tenantContext->schoolId())
                    ->ignore($campus?->id),
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