<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassLevel extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'sort_order',
        'status',
        'metadata',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    public function arms()
    {
        return $this->hasMany(ClassArm::class);
    }
}
