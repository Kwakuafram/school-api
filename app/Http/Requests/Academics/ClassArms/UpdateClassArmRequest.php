<?php

namespace App\Http\Requests\Academics\ClassArms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassArmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
