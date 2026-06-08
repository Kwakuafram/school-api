<?php

namespace App\Http\Requests\Students;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $student = $this->route('student');

        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => [
                'sometimes',
                'nullable',
                'uuid',
                Rule::exists('campuses', 'id')
                    ->where('school_id', $tenantContext->schoolId()),
            ],

            'admission_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('students', 'admission_number')
                    ->where('school_id', $tenantContext->schoolId())
                    ->ignore($student?->id),
            ],

            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'middle_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'preferred_name' => ['sometimes', 'nullable', 'string', 'max:255'],

            'gender' => ['sometimes', 'nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],

            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],

            'nationality' => ['sometimes', 'nullable', 'string', 'max:100'],
            'religion' => ['sometimes', 'nullable', 'string', 'max:100'],
            'blood_group' => ['sometimes', 'nullable', 'string', 'max:10'],

            'address' => ['sometimes', 'nullable', 'string'],
            'photo_path' => ['sometimes', 'nullable', 'string', 'max:255'],
            'admission_date' => ['sometimes', 'nullable', 'date'],

            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'graduated', 'transferred', 'withdrawn'])],

            'medical_info' => ['sometimes', 'nullable', 'array'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
