<?php

namespace App\Http\Requests\Students;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
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
            'campus_id' => [
                'nullable',
                'uuid',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $tenantContext->schoolId()),
            ],

            'admission_number' => [
                'required',
                'string',
                'max:100',
                Rule::unique('students', 'admission_number')
                    ->where('school_id', $tenantContext->schoolId()),
            ],

            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'preferred_name' => ['nullable', 'string', 'max:255'],

            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date'],

            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],

            'nationality' => ['nullable', 'string', 'max:100'],
            'religion' => ['nullable', 'string', 'max:100'],
            'blood_group' => ['nullable', 'string', 'max:10'],

            'address' => ['nullable', 'string'],
            'photo_path' => ['nullable', 'string', 'max:255'],
            'admission_date' => ['nullable', 'date'],

            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],

            'medical_info' => ['nullable', 'array'],
            'metadata' => ['nullable', 'array'],

            'enrollment' => ['nullable', 'array'],
            'enrollment.academic_year_id' => [
                'required_with:enrollment',
                'uuid',
                Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'enrollment.academic_term_id' => [
                'nullable',
                'uuid',
                Rule::exists('academic_terms', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'enrollment.class_level_id' => [
                'nullable',
                'uuid',
                Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'enrollment.class_arm_id' => [
                'nullable',
                'uuid',
                Rule::exists('class_arms', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'enrollment.enrolled_at' => ['nullable', 'date'],
        ];
    }
}
