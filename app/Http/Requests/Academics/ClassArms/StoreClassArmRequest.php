<?php

namespace App\Http\Requests\Academics\ClassArms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassArmRequest extends FormRequest
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
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
