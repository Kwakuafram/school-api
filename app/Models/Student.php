<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'campus_id',
        'current_enrollment_id',
        'admission_number',
        'first_name',
        'middle_name',
        'last_name',
        'preferred_name',
        'gender',
        'date_of_birth',
        'email',
        'phone',
        'nationality',
        'religion',
        'blood_group',
        'address',
        'photo_path',
        'admission_date',
        'status',
        'medical_info',
        'metadata',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'medical_info' => 'array',
        'metadata' => 'array',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot([
                'relationship',
                'is_primary',
                'can_pick_up',
                'receives_sms',
                'receives_email',
            ])
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    public function currentEnrollment()
    {
        return $this->belongsTo(StudentEnrollment::class, 'current_enrollment_id');
    }

    public function getCurrentEnrollmentRecord(): ?StudentEnrollment
    {
        if ($this->relationLoaded('currentEnrollment')) {
            return $this->currentEnrollment;
        }

        if ($this->current_enrollment_id) {
            return $this->currentEnrollment()->first();
        }

        return $this->enrollments()
            ->where('status', 'active')
            ->latest('created_at')
            ->first();
    }

    public function getFullNameAttribute(): string
    {
        return trim(collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        ])->filter()->implode(' '));
    }
}
