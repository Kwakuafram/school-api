<?php

namespace App\Http\Requests\Academics\TeacherAssignments;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherAssignmentRequest extends FormRequest
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
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId())],
            'academic_term_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_arm_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $tenantContext->schoolId())],
            'subject_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $tenantContext->schoolId())],
            'teacher_user_id' => ['sometimes', 'required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $tenantContext->schoolId())],
            'assignment_type' => ['sometimes', 'required', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
