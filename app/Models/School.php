<?php

namespace App\Models;

use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use HasUuidPrimaryKey, SoftDeletes, RecordsAuditLogs;

    protected $fillable = [
        'name',
        'code',
        'email',
        'phone',
        'website',
        'country',
        'region',
        'city',
        'address',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function campuses()
    {
        return $this->hasMany(Campus::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['role_context', 'is_default'])
            ->withTimestamps();
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}