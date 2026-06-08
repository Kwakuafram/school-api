<?php

namespace App\Http\Requests\Students;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'relationship' => ['sometimes', 'required', 'string', 'max:50'],

            'is_primary' => ['sometimes', 'boolean'],
            'can_pick_up' => ['sometimes', 'boolean'],
            'receives_sms' => ['sometimes', 'boolean'],
            'receives_email' => ['sometimes', 'boolean'],
        ];
    }
}
