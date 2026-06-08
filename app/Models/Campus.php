<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Campus extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'email',
        'phone',
        'country',
        'region',
        'city',
        'address',
        'is_main',
        'status',
        'settings',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'settings' => 'array',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
