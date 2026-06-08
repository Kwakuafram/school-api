<?php

namespace App\Http\Requests\Students;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AttachGuardianRequest extends FormRequest
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
            'guardian_id' => [
                'required',
                'uuid',
                Rule::exists('guardians', 'id')
                    ->where('school_id', $tenantContext->schoolId()),
            ],

            'relationship' => ['required', 'string', 'max:50'],

            'is_primary' => ['nullable', 'boolean'],
            'can_pick_up' => ['nullable', 'boolean'],
            'receives_sms' => ['nullable', 'boolean'],
            'receives_email' => ['nullable', 'boolean'],
        ];
    }
}
