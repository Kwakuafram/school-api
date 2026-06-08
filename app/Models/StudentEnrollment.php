<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentEnrollment extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'campus_id',
        'student_id',
        'academic_year',
        'term',
        'class_level',
        'class_arm',
        'enrolled_at',
        'exited_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'exited_at' => 'date',
        'metadata' => 'array',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
