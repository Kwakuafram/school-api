<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicTerm extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'name',
        'start_date',
        'end_date',
        'is_current',
        'status',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'metadata' => 'array',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
