<?php

namespace App\Http\Requests\Attendance;

use App\Models\AttendanceSession;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $schoolId = app(TenantContext::class)->schoolId();
        $period = $this->input('period', 'morning');

        return [
            'class_level_id' => [
                'required_without:class_arm_id',
                'nullable',
                'uuid',
                Rule::exists('class_levels', 'id')->where('school_id', $schoolId),
            ],

            'class_arm_id' => [
                'nullable',
                'uuid',
                Rule::exists('class_arms', 'id')->where('school_id', $schoolId),
            ],

            'campus_id' => [
                'nullable',
                'uuid',
                Rule::exists('campuses', 'id')->where('school_id', $schoolId),
            ],

            'academic_year_id' => [
                'required',
                'uuid',
                Rule::exists('academic_years', 'id')->where('school_id', $schoolId),
            ],

            'academic_term_id' => [
                'nullable',
                'uuid',
                Rule::exists('academic_terms', 'id')->where('school_id', $schoolId),
            ],

            'session_date' => [
                'required',
                'date',
                function (string $attribute, mixed $value, \Closure $fail) use ($schoolId, $period) {
                    $date = Carbon::parse($value)->toDateString();

                    $query = AttendanceSession::query()
                        ->where('school_id', $schoolId)
                        ->where('period', $period)
                        ->whereDate('session_date', $date);

                    if ($this->filled('class_arm_id')) {
                        $query->where('class_arm_id', $this->input('class_arm_id'));
                    } else {
                        $query->whereNull('class_arm_id')
                            ->where('class_level_id', $this->input('class_level_id'));
                    }

                    if ($query->exists()) {
                        $fail('An attendance session already exists for this class, date, and period.');
                    }
                },
            ],

            'period' => [
                'sometimes',
                'string',
                Rule::in(['morning', 'afternoon', 'full_day']),
            ],

            'notes' => ['nullable', 'string', 'max:1000'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
