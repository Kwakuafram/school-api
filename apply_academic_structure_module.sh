#!/usr/bin/env sh
set -eu

echo "Creating tenant-scoped Academic Structure module..."

mkdir -p app/Models
mkdir -p app/Services/Academics
mkdir -p app/Http/Controllers/Api/V1
mkdir -p app/Http/Requests/Academics
mkdir -p app/Http/Resources
mkdir -p database/migrations
mkdir -p database/seeders
mkdir -p tests/Feature/Models

make_migration() {
    file_name="$1"
    body="$2"

    if ls database/migrations/*_"$file_name".php >/dev/null 2>&1; then
        echo "Migration $file_name already exists. Skipping."
        return
    fi

    timestamp="$(date +%Y_%m_%d_%H%M%S)"
    path="database/migrations/${timestamp}_${file_name}.php"
    printf "%s" "$body" > "$path"
    echo "Created migration: $path"
    sleep 1
}

make_migration "create_academic_years_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''academic_years'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->string('\''name'\'', 50);
            $table->date('\''start_date'\'');
            $table->date('\''end_date'\'');
            $table->boolean('\''is_current'\'')->default(false);
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['\''school_id'\'', '\''name'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
            $table->index(['\''school_id'\'', '\''is_current'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''academic_years'\'');
    }
};
'

make_migration "create_academic_terms_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''academic_terms'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->foreignUuid('\''academic_year_id'\'')
                ->constrained('\''academic_years'\'')
                ->cascadeOnDelete();

            $table->string('\''name'\'', 50);
            $table->date('\''start_date'\'');
            $table->date('\''end_date'\'');
            $table->boolean('\''is_current'\'')->default(false);
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['\''academic_year_id'\'', '\''name'\'']);
            $table->index(['\''school_id'\'', '\''academic_year_id'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
            $table->index(['\''school_id'\'', '\''is_current'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''academic_terms'\'');
    }
};
'

make_migration "create_class_levels_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''class_levels'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->string('\''name'\'', 100);
            $table->string('\''code'\'', 50);
            $table->unsignedInteger('\''sort_order'\'')->default(0);
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['\''school_id'\'', '\''code'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
            $table->index(['\''school_id'\'', '\''sort_order'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''class_levels'\'');
    }
};
'

make_migration "create_class_arms_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''class_arms'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->foreignUuid('\''campus_id'\'')
                ->nullable()
                ->constrained('\''campuses'\'')
                ->nullOnDelete();

            $table->foreignUuid('\''class_level_id'\'')
                ->constrained('\''class_levels'\'')
                ->cascadeOnDelete();

            $table->string('\''name'\'', 100);
            $table->string('\''code'\'', 50);
            $table->unsignedInteger('\''capacity'\'')->nullable();
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['\''school_id'\'', '\''class_level_id'\'', '\''code'\'']);
            $table->index(['\''school_id'\'', '\''campus_id'\'']);
            $table->index(['\''school_id'\'', '\''class_level_id'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''class_arms'\'');
    }
};
'

make_migration "create_subjects_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''subjects'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->string('\''name'\'', 150);
            $table->string('\''code'\'', 50);
            $table->string('\''type'\'', 30)->default('\''core'\'');
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['\''school_id'\'', '\''code'\'']);
            $table->index(['\''school_id'\'', '\''type'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''subjects'\'');
    }
};
'

make_migration "create_teacher_assignments_table" '<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('\''teacher_assignments'\'', function (Blueprint $table) {
            $table->uuid('\''id'\'')->primary();

            $table->foreignUuid('\''school_id'\'')
                ->constrained('\''schools'\'')
                ->cascadeOnDelete();

            $table->foreignUuid('\''campus_id'\'')
                ->nullable()
                ->constrained('\''campuses'\'')
                ->nullOnDelete();

            $table->foreignUuid('\''academic_year_id'\'')
                ->constrained('\''academic_years'\'')
                ->cascadeOnDelete();

            $table->foreignUuid('\''academic_term_id'\'')
                ->nullable()
                ->constrained('\''academic_terms'\'')
                ->nullOnDelete();

            $table->foreignUuid('\''class_level_id'\'')
                ->constrained('\''class_levels'\'')
                ->cascadeOnDelete();

            $table->foreignUuid('\''class_arm_id'\'')
                ->nullable()
                ->constrained('\''class_arms'\'')
                ->nullOnDelete();

            $table->foreignUuid('\''subject_id'\'')
                ->nullable()
                ->constrained('\''subjects'\'')
                ->nullOnDelete();

            $table->foreignUuid('\''teacher_user_id'\'')
                ->constrained('\''users'\'')
                ->cascadeOnDelete();

            $table->string('\''assignment_type'\'', 50)->default('\''subject_teacher'\'');
            $table->date('\''starts_at'\'')->nullable();
            $table->date('\''ends_at'\'')->nullable();
            $table->string('\''status'\'', 30)->default('\''active'\'');
            $table->jsonb('\''metadata'\'')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['\''school_id'\'', '\''teacher_user_id'\'']);
            $table->index(['\''school_id'\'', '\''academic_year_id'\'', '\''academic_term_id'\'']);
            $table->index(['\''school_id'\'', '\''class_level_id'\'', '\''class_arm_id'\'']);
            $table->index(['\''school_id'\'', '\''subject_id'\'']);
            $table->index(['\''school_id'\'', '\''status'\'']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('\''teacher_assignments'\'');
    }
};
'

cat > app/Models/AcademicYear.php <<'PHP'
<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcademicYear extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
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

    public function terms()
    {
        return $this->hasMany(AcademicTerm::class);
    }

    public function teacherAssignments()
    {
        return $this->hasMany(TeacherAssignment::class);
    }
}
PHP

cat > app/Models/AcademicTerm.php <<'PHP'
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
PHP

cat > app/Models/ClassLevel.php <<'PHP'
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
PHP

cat > app/Models/ClassArm.php <<'PHP'
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
PHP

cat > app/Models/Subject.php <<'PHP'
<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'name',
        'code',
        'type',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];
}
PHP

cat > app/Models/TeacherAssignment.php <<'PHP'
<?php

namespace App\Models;

use App\Models\Traits\BelongsToSchool;
use App\Models\Traits\HasUuidPrimaryKey;
use App\Models\Traits\RecordsAuditLogs;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherAssignment extends Model
{
    use BelongsToSchool, HasUuidPrimaryKey, RecordsAuditLogs, SoftDeletes;

    protected $fillable = [
        'school_id',
        'campus_id',
        'academic_year_id',
        'academic_term_id',
        'class_level_id',
        'class_arm_id',
        'subject_id',
        'teacher_user_id',
        'assignment_type',
        'starts_at',
        'ends_at',
        'status',
        'metadata',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
        'metadata' => 'array',
    ];

    public function campus()
    {
        return $this->belongsTo(Campus::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function academicTerm()
    {
        return $this->belongsTo(AcademicTerm::class);
    }

    public function classLevel()
    {
        return $this->belongsTo(ClassLevel::class);
    }

    public function classArm()
    {
        return $this->belongsTo(ClassArm::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_user_id');
    }
}
PHP

cat > app/Http/Resources/AcademicYearResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => (bool) $this->is_current,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'terms' => AcademicTermResource::collection($this->whenLoaded('terms')),
        ];
    }
}
PHP

cat > app/Http/Resources/AcademicTermResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicTermResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'academic_year_id' => $this->academic_year_id,
            'name' => $this->name,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'is_current' => (bool) $this->is_current,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
        ];
    }
}
PHP

cat > app/Http/Resources/ClassLevelResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'sort_order' => $this->sort_order,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'arms' => ClassArmResource::collection($this->whenLoaded('arms')),
        ];
    }
}
PHP

cat > app/Http/Resources/ClassArmResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClassArmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'class_level_id' => $this->class_level_id,
            'name' => $this->name,
            'code' => $this->code,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'class_level' => new ClassLevelResource($this->whenLoaded('classLevel')),
        ];
    }
}
PHP

cat > app/Http/Resources/SubjectResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
PHP

cat > app/Http/Resources/TeacherAssignmentResource.php <<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherAssignmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'campus_id' => $this->campus_id,
            'academic_year_id' => $this->academic_year_id,
            'academic_term_id' => $this->academic_term_id,
            'class_level_id' => $this->class_level_id,
            'class_arm_id' => $this->class_arm_id,
            'subject_id' => $this->subject_id,
            'teacher_user_id' => $this->teacher_user_id,
            'assignment_type' => $this->assignment_type,
            'starts_at' => $this->starts_at?->toDateString(),
            'ends_at' => $this->ends_at?->toDateString(),
            'status' => $this->status,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            'campus' => new CampusResource($this->whenLoaded('campus')),
            'academic_year' => new AcademicYearResource($this->whenLoaded('academicYear')),
            'academic_term' => new AcademicTermResource($this->whenLoaded('academicTerm')),
            'class_level' => new ClassLevelResource($this->whenLoaded('classLevel')),
            'class_arm' => new ClassArmResource($this->whenLoaded('classArm')),
            'subject' => new SubjectResource($this->whenLoaded('subject')),
            'teacher' => new UserResource($this->whenLoaded('teacher')),
        ];
    }
}
PHP

cat > app/Services/Academics/AcademicCrudService.php <<'PHP'
<?php

namespace App\Services\Academics;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Services\Tenancy\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AcademicCrudService
{
    public function __construct(
        private readonly TenantContext $tenantContext
    ) {}

    public function list(string $modelClass, array $filters = [], array $with = []): LengthAwarePaginator
    {
        /** @var class-string<Model> $modelClass */
        return $modelClass::query()
            ->with($with)
            ->forCurrentSchool()
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['search'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('name', 'ILIKE', "%{$search}%");

                    if ($this->columnExists($query, 'code')) {
                        $query->orWhere('code', 'ILIKE', "%{$search}%");
                    }
                });
            })
            ->when($filters['academic_year_id'] ?? null, fn (Builder $query, string $id) => $query->where('academic_year_id', $id))
            ->when($filters['academic_term_id'] ?? null, fn (Builder $query, string $id) => $query->where('academic_term_id', $id))
            ->when($filters['campus_id'] ?? null, fn (Builder $query, string $id) => $query->where('campus_id', $id))
            ->when($filters['class_level_id'] ?? null, fn (Builder $query, string $id) => $query->where('class_level_id', $id))
            ->when($filters['class_arm_id'] ?? null, fn (Builder $query, string $id) => $query->where('class_arm_id', $id))
            ->when($filters['subject_id'] ?? null, fn (Builder $query, string $id) => $query->where('subject_id', $id))
            ->latest()
            ->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function create(string $modelClass, array $data, array $with = []): Model
    {
        return DB::transaction(function () use ($modelClass, $data, $with) {
            $schoolId = $this->tenantContext->requireSchoolId();

            $this->resetCurrentFlagsIfNeeded($modelClass, $schoolId, $data);

            /** @var Model $record */
            $record = $modelClass::query()->create([
                ...$data,
                'school_id' => $schoolId,
                'status' => $data['status'] ?? 'active',
            ]);

            return $record->load($with);
        });
    }

    public function update(Model $record, array $data, array $with = []): Model
    {
        return DB::transaction(function () use ($record, $data, $with) {
            $this->abortIfOutsideTenant($record);

            $this->resetCurrentFlagsIfNeeded($record::class, $record->school_id, $data, $record->getKey());

            $record->update($data);

            return $record->refresh()->load($with);
        });
    }

    public function delete(Model $record): void
    {
        DB::transaction(function () use ($record) {
            $this->abortIfOutsideTenant($record);
            $record->delete();
        });
    }

    private function abortIfOutsideTenant(Model $record): void
    {
        abort_unless(
            isset($record->school_id) && $record->school_id === $this->tenantContext->requireSchoolId(),
            403,
            'You cannot manage this resource outside the current school.'
        );
    }

    private function resetCurrentFlagsIfNeeded(string $modelClass, string $schoolId, array $data, ?string $exceptId = null): void
    {
        if (! in_array($modelClass, [AcademicYear::class, AcademicTerm::class], true)) {
            return;
        }

        if (! (bool) ($data['is_current'] ?? false)) {
            return;
        }

        $query = $modelClass::query()
            ->where('school_id', $schoolId);

        if ($modelClass === AcademicTerm::class && ! empty($data['academic_year_id'])) {
            $query->where('academic_year_id', $data['academic_year_id']);
        }

        if ($exceptId) {
            $query->whereKeyNot($exceptId);
        }

        $query->update(['is_current' => false]);
    }

    private function columnExists(Builder $query, string $column): bool
    {
        $model = $query->getModel();

        return in_array($column, $model->getFillable(), true);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/AcademicStructureController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicTermResource;
use App\Http\Resources\AcademicYearResource;
use App\Http\Resources\ClassArmResource;
use App\Http\Resources\ClassLevelResource;
use App\Http\Resources\SubjectResource;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class AcademicStructureController extends Controller
{
    public function __construct(
        private readonly AcademicCrudService $academicCrudService
    ) {}

    public function academicYears(Request $request): AnonymousResourceCollection
    {
        return AcademicYearResource::collection(
            $this->academicCrudService->list(AcademicYear::class, $request->only(['status', 'search', 'per_page']), ['terms'])
        );
    }

    public function storeAcademicYear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(AcademicYear::class, $data, ['terms']);

        return response()->json([
            'message' => 'Academic year created successfully.',
            'data' => new AcademicYearResource($record),
        ], 201);
    }

    public function showAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        return response()->json([
            'data' => new AcademicYearResource($academicYear->load('terms')),
        ]);
    }

    public function updateAcademicYear(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($academicYear, $data, ['terms']);

        return response()->json([
            'message' => 'Academic year updated successfully.',
            'data' => new AcademicYearResource($record),
        ]);
    }

    public function destroyAcademicYear(AcademicYear $academicYear): JsonResponse
    {
        $this->academicCrudService->delete($academicYear);

        return response()->json(['message' => 'Academic year deleted successfully.']);
    }

    public function academicTerms(Request $request): AnonymousResourceCollection
    {
        return AcademicTermResource::collection(
            $this->academicCrudService->list(AcademicTerm::class, $request->only(['academic_year_id', 'status', 'search', 'per_page']), ['academicYear'])
        );
    }

    public function storeAcademicTerm(Request $request): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(AcademicTerm::class, $data, ['academicYear']);

        return response()->json([
            'message' => 'Academic term created successfully.',
            'data' => new AcademicTermResource($record),
        ], 201);
    }

    public function showAcademicTerm(AcademicTerm $academicTerm): JsonResponse
    {
        return response()->json(['data' => new AcademicTermResource($academicTerm->load('academicYear'))]);
    }

    public function updateAcademicTerm(Request $request, AcademicTerm $academicTerm): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($academicTerm, $data, ['academicYear']);

        return response()->json([
            'message' => 'Academic term updated successfully.',
            'data' => new AcademicTermResource($record),
        ]);
    }

    public function destroyAcademicTerm(AcademicTerm $academicTerm): JsonResponse
    {
        $this->academicCrudService->delete($academicTerm);

        return response()->json(['message' => 'Academic term deleted successfully.']);
    }

    public function classLevels(Request $request): AnonymousResourceCollection
    {
        return ClassLevelResource::collection(
            $this->academicCrudService->list(ClassLevel::class, $request->only(['status', 'search', 'per_page']), ['arms'])
        );
    }

    public function storeClassLevel(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(ClassLevel::class, $data, ['arms']);

        return response()->json([
            'message' => 'Class level created successfully.',
            'data' => new ClassLevelResource($record),
        ], 201);
    }

    public function showClassLevel(ClassLevel $classLevel): JsonResponse
    {
        return response()->json(['data' => new ClassLevelResource($classLevel->load('arms'))]);
    }

    public function updateClassLevel(Request $request, ClassLevel $classLevel): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($classLevel, $data, ['arms']);

        return response()->json([
            'message' => 'Class level updated successfully.',
            'data' => new ClassLevelResource($record),
        ]);
    }

    public function destroyClassLevel(ClassLevel $classLevel): JsonResponse
    {
        $this->academicCrudService->delete($classLevel);

        return response()->json(['message' => 'Class level deleted successfully.']);
    }

    public function classArms(Request $request): AnonymousResourceCollection
    {
        return ClassArmResource::collection(
            $this->academicCrudService->list(ClassArm::class, $request->only(['campus_id', 'class_level_id', 'status', 'search', 'per_page']), ['campus', 'classLevel'])
        );
    }

    public function storeClassArm(Request $request): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(ClassArm::class, $data, ['campus', 'classLevel']);

        return response()->json([
            'message' => 'Class arm created successfully.',
            'data' => new ClassArmResource($record),
        ], 201);
    }

    public function showClassArm(ClassArm $classArm): JsonResponse
    {
        return response()->json(['data' => new ClassArmResource($classArm->load(['campus', 'classLevel']))]);
    }

    public function updateClassArm(Request $request, ClassArm $classArm): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($classArm, $data, ['campus', 'classLevel']);

        return response()->json([
            'message' => 'Class arm updated successfully.',
            'data' => new ClassArmResource($record),
        ]);
    }

    public function destroyClassArm(ClassArm $classArm): JsonResponse
    {
        $this->academicCrudService->delete($classArm);

        return response()->json(['message' => 'Class arm deleted successfully.']);
    }

    public function subjects(Request $request): AnonymousResourceCollection
    {
        return SubjectResource::collection(
            $this->academicCrudService->list(Subject::class, $request->only(['status', 'search', 'per_page']))
        );
    }

    public function storeSubject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(Subject::class, $data);

        return response()->json([
            'message' => 'Subject created successfully.',
            'data' => new SubjectResource($record),
        ], 201);
    }

    public function showSubject(Subject $subject): JsonResponse
    {
        return response()->json(['data' => new SubjectResource($subject)]);
    }

    public function updateSubject(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($subject, $data);

        return response()->json([
            'message' => 'Subject updated successfully.',
            'data' => new SubjectResource($record),
        ]);
    }

    public function destroySubject(Subject $subject): JsonResponse
    {
        $this->academicCrudService->delete($subject);

        return response()->json(['message' => 'Subject deleted successfully.']);
    }

    public function teacherAssignments(Request $request): AnonymousResourceCollection
    {
        return TeacherAssignmentResource::collection(
            $this->academicCrudService->list(TeacherAssignment::class, $request->only([
                'academic_year_id',
                'academic_term_id',
                'campus_id',
                'class_level_id',
                'class_arm_id',
                'subject_id',
                'status',
                'per_page',
            ]), ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher'])
        );
    }

    public function storeTeacherAssignment(Request $request): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'academic_term_id' => ['nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'class_arm_id' => ['nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $schoolId)],
            'assignment_type' => ['nullable', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ]);

        $record = $this->academicCrudService->create(TeacherAssignment::class, $data, ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher']);

        return response()->json([
            'message' => 'Teacher assignment created successfully.',
            'data' => new TeacherAssignmentResource($record),
        ], 201);
    }

    public function showTeacherAssignment(TeacherAssignment $teacherAssignment): JsonResponse
    {
        return response()->json([
            'data' => new TeacherAssignmentResource(
                $teacherAssignment->load(['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher'])
            ),
        ]);
    }

    public function updateTeacherAssignment(Request $request, TeacherAssignment $teacherAssignment): JsonResponse
    {
        $schoolId = app(\App\Services\Tenancy\TenantContext::class)->requireSchoolId();

        $data = $request->validate([
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $schoolId)],
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $schoolId)],
            'academic_term_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $schoolId)],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $schoolId)],
            'class_arm_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $schoolId)],
            'subject_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $schoolId)],
            'teacher_user_id' => ['sometimes', 'required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $schoolId)],
            'assignment_type' => ['sometimes', 'required', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ]);

        $record = $this->academicCrudService->update($teacherAssignment, $data, ['campus', 'academicYear', 'academicTerm', 'classLevel', 'classArm', 'subject', 'teacher']);

        return response()->json([
            'message' => 'Teacher assignment updated successfully.',
            'data' => new TeacherAssignmentResource($record),
        ]);
    }

    public function destroyTeacherAssignment(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->academicCrudService->delete($teacherAssignment);

        return response()->json(['message' => 'Teacher assignment deleted successfully.']);
    }
}
PHP

cat > database/seeders/AcademicPermissionSeeder.php <<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AcademicPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionNames = [
            'academics.view',
            'academics.create',
            'academics.update',
            'academics.delete',
            'teacher_assignments.view',
            'teacher_assignments.create',
            'teacher_assignments.update',
            'teacher_assignments.delete',
        ];

        $permissions = collect($permissionNames)
            ->mapWithKeys(fn (string $name) => [
                $name => Permission::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]),
            ]);

        foreach (['super_admin', 'school_admin', 'principal'] as $roleName) {
            $role = Role::query()
                ->where('name', $roleName)
                ->where('guard_name', 'web')
                ->first();

            if ($role) {
                $role->givePermissionTo($permissions->values()->all());
            }
        }

        $teacher = Role::query()
            ->where('name', 'teacher')
            ->where('guard_name', 'web')
            ->first();

        if ($teacher) {
            $teacher->givePermissionTo([
                $permissions['academics.view'],
                $permissions['teacher_assignments.view'],
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
PHP

python3 - <<'PY'
from pathlib import Path

path = Path("routes/api.php")
text = path.read_text()

if "AcademicStructureController" not in text:
    import_line = "use App\\Http\\Controllers\\Api\\V1\\AcademicStructureController;"
    marker = "use Illuminate\\Support\\Facades\\Route;"
    text = text.replace(marker, import_line + "\n" + marker)

routes = """
            Route::middleware('permission:academics.view')->get('academic-years', [AcademicStructureController::class, 'academicYears']);
            Route::middleware('permission:academics.create')->post('academic-years', [AcademicStructureController::class, 'storeAcademicYear']);
            Route::middleware('permission:academics.view')->get('academic-years/{academicYear}', [AcademicStructureController::class, 'showAcademicYear']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'academic-years/{academicYear}', [AcademicStructureController::class, 'updateAcademicYear']);
            Route::middleware('permission:academics.delete')->delete('academic-years/{academicYear}', [AcademicStructureController::class, 'destroyAcademicYear']);

            Route::middleware('permission:academics.view')->get('academic-terms', [AcademicStructureController::class, 'academicTerms']);
            Route::middleware('permission:academics.create')->post('academic-terms', [AcademicStructureController::class, 'storeAcademicTerm']);
            Route::middleware('permission:academics.view')->get('academic-terms/{academicTerm}', [AcademicStructureController::class, 'showAcademicTerm']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'academic-terms/{academicTerm}', [AcademicStructureController::class, 'updateAcademicTerm']);
            Route::middleware('permission:academics.delete')->delete('academic-terms/{academicTerm}', [AcademicStructureController::class, 'destroyAcademicTerm']);

            Route::middleware('permission:academics.view')->get('class-levels', [AcademicStructureController::class, 'classLevels']);
            Route::middleware('permission:academics.create')->post('class-levels', [AcademicStructureController::class, 'storeClassLevel']);
            Route::middleware('permission:academics.view')->get('class-levels/{classLevel}', [AcademicStructureController::class, 'showClassLevel']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'class-levels/{classLevel}', [AcademicStructureController::class, 'updateClassLevel']);
            Route::middleware('permission:academics.delete')->delete('class-levels/{classLevel}', [AcademicStructureController::class, 'destroyClassLevel']);

            Route::middleware('permission:academics.view')->get('class-arms', [AcademicStructureController::class, 'classArms']);
            Route::middleware('permission:academics.create')->post('class-arms', [AcademicStructureController::class, 'storeClassArm']);
            Route::middleware('permission:academics.view')->get('class-arms/{classArm}', [AcademicStructureController::class, 'showClassArm']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'class-arms/{classArm}', [AcademicStructureController::class, 'updateClassArm']);
            Route::middleware('permission:academics.delete')->delete('class-arms/{classArm}', [AcademicStructureController::class, 'destroyClassArm']);

            Route::middleware('permission:academics.view')->get('subjects', [AcademicStructureController::class, 'subjects']);
            Route::middleware('permission:academics.create')->post('subjects', [AcademicStructureController::class, 'storeSubject']);
            Route::middleware('permission:academics.view')->get('subjects/{subject}', [AcademicStructureController::class, 'showSubject']);
            Route::middleware('permission:academics.update')->match(['put', 'patch'], 'subjects/{subject}', [AcademicStructureController::class, 'updateSubject']);
            Route::middleware('permission:academics.delete')->delete('subjects/{subject}', [AcademicStructureController::class, 'destroySubject']);

            Route::middleware('permission:teacher_assignments.view')->get('teacher-assignments', [AcademicStructureController::class, 'teacherAssignments']);
            Route::middleware('permission:teacher_assignments.create')->post('teacher-assignments', [AcademicStructureController::class, 'storeTeacherAssignment']);
            Route::middleware('permission:teacher_assignments.view')->get('teacher-assignments/{teacherAssignment}', [AcademicStructureController::class, 'showTeacherAssignment']);
            Route::middleware('permission:teacher_assignments.update')->match(['put', 'patch'], 'teacher-assignments/{teacherAssignment}', [AcademicStructureController::class, 'updateTeacherAssignment']);
            Route::middleware('permission:teacher_assignments.delete')->delete('teacher-assignments/{teacherAssignment}', [AcademicStructureController::class, 'destroyTeacherAssignment']);
"""

if "academic-years" not in text:
    marker = "            Route::middleware('permission:audit_logs.view')->get('audit-logs'"
    if marker in text:
        text = text.replace(marker, routes + "\n" + marker)
    else:
        marker = "        });\n    });\n});"
        text = text.replace(marker, routes + "\n        });\n    });\n});")

path.write_text(text)
PY

cat > tests/Feature/Models/AcademicStructureModelsTest.php <<'PHP'
<?php

namespace Tests\Feature\Models;

use App\Models\AcademicTerm;
use App\Models\AcademicYear;
use App\Models\Campus;
use App\Models\ClassArm;
use App\Models\ClassLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicStructureModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_structure_models_can_be_created_and_related(): void
    {
        $school = School::query()->create([
            'name' => 'Demo School',
            'code' => 'DIS',
            'status' => 'active',
        ]);

        $campus = Campus::query()->create([
            'school_id' => $school->id,
            'name' => 'Main Campus',
            'code' => 'MAIN',
            'status' => 'active',
        ]);

        $teacher = User::query()->create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.test',
            'password' => 'password123',
            'status' => 'active',
        ]);

        $teacher->schools()->attach($school->id, [
            'role_context' => 'teacher',
            'is_default' => true,
        ]);

        $academicYear = AcademicYear::query()->create([
            'school_id' => $school->id,
            'name' => '2026/2027',
            'start_date' => '2026-09-01',
            'end_date' => '2027-07-31',
            'is_current' => true,
            'status' => 'active',
        ]);

        $term = AcademicTerm::query()->create([
            'school_id' => $school->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'Term 1',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-20',
            'is_current' => true,
            'status' => 'active',
        ]);

        $level = ClassLevel::query()->create([
            'school_id' => $school->id,
            'name' => 'Basic 1',
            'code' => 'B1',
            'sort_order' => 1,
            'status' => 'active',
        ]);

        $arm = ClassArm::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'class_level_id' => $level->id,
            'name' => 'Basic 1 A',
            'code' => 'A',
            'capacity' => 35,
            'status' => 'active',
        ]);

        $subject = Subject::query()->create([
            'school_id' => $school->id,
            'name' => 'Mathematics',
            'code' => 'MATH',
            'type' => 'core',
            'status' => 'active',
        ]);

        $assignment = TeacherAssignment::query()->create([
            'school_id' => $school->id,
            'campus_id' => $campus->id,
            'academic_year_id' => $academicYear->id,
            'academic_term_id' => $term->id,
            'class_level_id' => $level->id,
            'class_arm_id' => $arm->id,
            'subject_id' => $subject->id,
            'teacher_user_id' => $teacher->id,
            'assignment_type' => 'subject_teacher',
            'starts_at' => '2026-09-01',
            'status' => 'active',
        ]);

        $this->assertSame($academicYear->id, $term->academicYear->id);
        $this->assertSame($level->id, $arm->classLevel->id);
        $this->assertSame($campus->id, $arm->campus->id);
        $this->assertSame($teacher->id, $assignment->teacher->id);
        $this->assertSame($subject->id, $assignment->subject->id);
        $this->assertSame($arm->id, $assignment->classArm->id);
    }
}
PHP

echo "Running Pint..."
if [ -f "./vendor/bin/pint" ]; then
    ./vendor/bin/pint
else
    echo "Pint not found locally. Run: docker compose exec app ./vendor/bin/pint"
fi

echo "Academic Structure module created."
echo ""
echo "Next commands:"
echo "docker compose exec app php artisan migrate"
echo "docker compose exec app php artisan db:seed --class=AcademicPermissionSeeder"
echo "docker compose exec app php artisan permission:cache-reset"
echo "docker compose exec app php artisan optimize:clear"
echo "docker compose exec app php artisan test --filter=AcademicStructureModelsTest"
echo "docker compose exec app ./vendor/bin/pint --test"
