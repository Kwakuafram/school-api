<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassArm extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'campus_id',
        'class_level_id',
        'name',
        'code',
        'capacity',
        'status',
        'metadata',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'metadata' => 'array',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function classLevel()
    {
        return $this->belongsTo(ClassLevel::class);
    }
}
