<?php

namespace App\Http\Requests\Attendance;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = app(TenantContext::class)->schoolId();

        return [
            'attendances' => ['required', 'array', 'min:1'],

            'attendances.*.student_id' => [
                'required',
                'uuid',
                Rule::exists('students', 'id')->where('school_id', $schoolId),
            ],

            'attendances.*.status' => [
                'required',
                'string',
                Rule::in(['present', 'absent', 'late', 'excused']),
            ],

            'attendances.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
