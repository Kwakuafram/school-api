#!/usr/bin/env sh
set -eu

echo "Refactoring Academic Structure validation into FormRequest classes and slim controllers..."

mkdir -p app/Http/Controllers/Api/V1/Academics
mkdir -p app/Http/Requests/Academics/AcademicYears
mkdir -p app/Http/Requests/Academics/AcademicTerms
mkdir -p app/Http/Requests/Academics/ClassLevels
mkdir -p app/Http/Requests/Academics/ClassArms
mkdir -p app/Http/Requests/Academics/Subjects
mkdir -p app/Http/Requests/Academics/TeacherAssignments

cat > app/Http/Requests/Academics/AcademicYears/StoreAcademicYearRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\AcademicYears;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/AcademicYears/UpdateAcademicYearRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\AcademicYears;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicYearRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/AcademicTerms/StoreAcademicTermRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\AcademicTerms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAcademicTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'academic_year_id' => [
                'required',
                'uuid',
                Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'name' => ['required', 'string', 'max:50'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_current' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/AcademicTerms/UpdateAcademicTermRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\AcademicTerms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAcademicTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'academic_year_id' => [
                'sometimes',
                'required',
                'uuid',
                Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId()),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:50'],
            'start_date' => ['sometimes', 'required', 'date'],
            'end_date' => ['sometimes', 'required', 'date'],
            'is_current' => ['sometimes', 'boolean'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive', 'archived'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/ClassLevels/StoreClassLevelRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\ClassLevels;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/ClassLevels/UpdateClassLevelRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\ClassLevels;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassLevelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/ClassArms/StoreClassArmRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\ClassArms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClassArmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/ClassArms/UpdateClassArmRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\ClassArms;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassArmRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'capacity' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/Subjects/StoreSubjectRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\Subjects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['required', 'string', 'max:50'],
            'type' => ['nullable', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/Subjects/UpdateSubjectRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\Subjects;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'code' => ['sometimes', 'required', 'string', 'max:50'],
            'type' => ['sometimes', 'required', 'string', Rule::in(['core', 'elective', 'extra_curricular'])],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/TeacherAssignments/StoreTeacherAssignmentRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\TeacherAssignments;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => ['nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'academic_year_id' => ['required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId())],
            'academic_term_id' => ['nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_arm_id' => ['nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $tenantContext->schoolId())],
            'subject_id' => ['nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $tenantContext->schoolId())],
            'teacher_user_id' => ['required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $tenantContext->schoolId())],
            'assignment_type' => ['nullable', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['nullable', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Requests/Academics/TeacherAssignments/UpdateTeacherAssignmentRequest.php <<'PHP'
<?php

namespace App\Http\Requests\Academics\TeacherAssignments;

use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);

        return [
            'campus_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('campuses', 'id')->where('school_id', $tenantContext->schoolId())],
            'academic_year_id' => ['sometimes', 'required', 'uuid', Rule::exists('academic_years', 'id')->where('school_id', $tenantContext->schoolId())],
            'academic_term_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('academic_terms', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_level_id' => ['sometimes', 'required', 'uuid', Rule::exists('class_levels', 'id')->where('school_id', $tenantContext->schoolId())],
            'class_arm_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('class_arms', 'id')->where('school_id', $tenantContext->schoolId())],
            'subject_id' => ['sometimes', 'nullable', 'uuid', Rule::exists('subjects', 'id')->where('school_id', $tenantContext->schoolId())],
            'teacher_user_id' => ['sometimes', 'required', 'uuid', Rule::exists('school_user', 'user_id')->where('school_id', $tenantContext->schoolId())],
            'assignment_type' => ['sometimes', 'required', 'string', Rule::in(['class_teacher', 'subject_teacher', 'assistant_teacher'])],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['sometimes', 'required', 'string', Rule::in(['active', 'inactive'])],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/AcademicYearController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\AcademicYears\StoreAcademicYearRequest;
use App\Http\Requests\Academics\AcademicYears\UpdateAcademicYearRequest;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcademicYearController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AcademicYearResource::collection($this->academicCrudService->list(AcademicYear::class, $request->only(['status', 'search', 'per_page']), ['terms']));
    }

    public function store(StoreAcademicYearRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(AcademicYear::class, $request->validated(), ['terms']);

        return response()->json(['message' => 'Academic year created successfully.', 'data' => new AcademicYearResource($record)], 201);
    }

    public function show(AcademicYear $academicYear): JsonResponse
    {
        return response()->json(['data' => new AcademicYearResource($academicYear->load('terms'))]);
    }

    public function update(UpdateAcademicYearRequest $request, AcademicYear $academicYear): JsonResponse
    {
        $record = $this->academicCrudService->update($academicYear, $request->validated(), ['terms']);

        return response()->json(['message' => 'Academic year updated successfully.', 'data' => new AcademicYearResource($record)]);
    }

    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        $this->academicCrudService->delete($academicYear);

        return response()->json(['message' => 'Academic year deleted successfully.']);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/AcademicTermController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\AcademicTerms\StoreAcademicTermRequest;
use App\Http\Requests\Academics\AcademicTerms\UpdateAcademicTermRequest;
use App\Http\Resources\AcademicTermResource;
use App\Models\AcademicTerm;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcademicTermController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return AcademicTermResource::collection($this->academicCrudService->list(AcademicTerm::class, $request->only(['academic_year_id', 'status', 'search', 'per_page']), ['academicYear']));
    }

    public function store(StoreAcademicTermRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(AcademicTerm::class, $request->validated(), ['academicYear']);

        return response()->json(['message' => 'Academic term created successfully.', 'data' => new AcademicTermResource($record)], 201);
    }

    public function show(AcademicTerm $academicTerm): JsonResponse
    {
        return response()->json(['data' => new AcademicTermResource($academicTerm->load('academicYear'))]);
    }

    public function update(UpdateAcademicTermRequest $request, AcademicTerm $academicTerm): JsonResponse
    {
        $record = $this->academicCrudService->update($academicTerm, $request->validated(), ['academicYear']);

        return response()->json(['message' => 'Academic term updated successfully.', 'data' => new AcademicTermResource($record)]);
    }

    public function destroy(AcademicTerm $academicTerm): JsonResponse
    {
        $this->academicCrudService->delete($academicTerm);

        return response()->json(['message' => 'Academic term deleted successfully.']);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/ClassLevelController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\ClassLevels\StoreClassLevelRequest;
use App\Http\Requests\Academics\ClassLevels\UpdateClassLevelRequest;
use App\Http\Resources\ClassLevelResource;
use App\Models\ClassLevel;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassLevelController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ClassLevelResource::collection($this->academicCrudService->list(ClassLevel::class, $request->only(['status', 'search', 'per_page']), ['arms']));
    }

    public function store(StoreClassLevelRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(ClassLevel::class, $request->validated(), ['arms']);

        return response()->json(['message' => 'Class level created successfully.', 'data' => new ClassLevelResource($record)], 201);
    }

    public function show(ClassLevel $classLevel): JsonResponse
    {
        return response()->json(['data' => new ClassLevelResource($classLevel->load('arms'))]);
    }

    public function update(UpdateClassLevelRequest $request, ClassLevel $classLevel): JsonResponse
    {
        $record = $this->academicCrudService->update($classLevel, $request->validated(), ['arms']);

        return response()->json(['message' => 'Class level updated successfully.', 'data' => new ClassLevelResource($record)]);
    }

    public function destroy(ClassLevel $classLevel): JsonResponse
    {
        $this->academicCrudService->delete($classLevel);

        return response()->json(['message' => 'Class level deleted successfully.']);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/ClassArmController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\ClassArms\StoreClassArmRequest;
use App\Http\Requests\Academics\ClassArms\UpdateClassArmRequest;
use App\Http\Resources\ClassArmResource;
use App\Models\ClassArm;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ClassArmController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return ClassArmResource::collection($this->academicCrudService->list(ClassArm::class, $request->only(['campus_id', 'class_level_id', 'status', 'search', 'per_page']), ['campus', 'classLevel']));
    }

    public function store(StoreClassArmRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(ClassArm::class, $request->validated(), ['campus', 'classLevel']);

        return response()->json(['message' => 'Class arm created successfully.', 'data' => new ClassArmResource($record)], 201);
    }

    public function show(ClassArm $classArm): JsonResponse
    {
        return response()->json(['data' => new ClassArmResource($classArm->load(['campus', 'classLevel']))]);
    }

    public function update(UpdateClassArmRequest $request, ClassArm $classArm): JsonResponse
    {
        $record = $this->academicCrudService->update($classArm, $request->validated(), ['campus', 'classLevel']);

        return response()->json(['message' => 'Class arm updated successfully.', 'data' => new ClassArmResource($record)]);
    }

    public function destroy(ClassArm $classArm): JsonResponse
    {
        $this->academicCrudService->delete($classArm);

        return response()->json(['message' => 'Class arm deleted successfully.']);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/SubjectController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\Subjects\StoreSubjectRequest;
use App\Http\Requests\Academics\Subjects\UpdateSubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return SubjectResource::collection($this->academicCrudService->list(Subject::class, $request->only(['status', 'search', 'per_page'])));
    }

    public function store(StoreSubjectRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(Subject::class, $request->validated());

        return response()->json(['message' => 'Subject created successfully.', 'data' => new SubjectResource($record)], 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        return response()->json(['data' => new SubjectResource($subject)]);
    }

    public function update(UpdateSubjectRequest $request, Subject $subject): JsonResponse
    {
        $record = $this->academicCrudService->update($subject, $request->validated());

        return response()->json(['message' => 'Subject updated successfully.', 'data' => new SubjectResource($record)]);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $this->academicCrudService->delete($subject);

        return response()->json(['message' => 'Subject deleted successfully.']);
    }
}
PHP

cat > app/Http/Controllers/Api/V1/Academics/TeacherAssignmentController.php <<'PHP'
<?php

namespace App\Http\Controllers\Api\V1\Academics;

use App\Http\Controllers\Controller;
use App\Http\Requests\Academics\TeacherAssignments\StoreTeacherAssignmentRequest;
use App\Http\Requests\Academics\TeacherAssignments\UpdateTeacherAssignmentRequest;
use App\Http\Resources\TeacherAssignmentResource;
use App\Models\TeacherAssignment;
use App\Services\Academics\AcademicCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeacherAssignmentController extends Controller
{
    private const RELATIONS = [
        'campus',
        'academicYear',
        'academicTerm',
        'classLevel',
        'classArm',
        'subject',
        'teacher',
    ];

    public function __construct(private readonly AcademicCrudService $academicCrudService) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        return TeacherAssignmentResource::collection($this->academicCrudService->list(TeacherAssignment::class, $request->only([
            'academic_year_id',
            'academic_term_id',
            'campus_id',
            'class_level_id',
            'class_arm_id',
            'subject_id',
            'status',
            'per_page',
        ]), self::RELATIONS));
    }

    public function store(StoreTeacherAssignmentRequest $request): JsonResponse
    {
        $record = $this->academicCrudService->create(TeacherAssignment::class, $request->validated(), self::RELATIONS);

        return response()->json(['message' => 'Teacher assignment created successfully.', 'data' => new TeacherAssignmentResource($record)], 201);
    }

    public function show(TeacherAssignment $teacherAssignment): JsonResponse
    {
        return response()->json(['data' => new TeacherAssignmentResource($teacherAssignment->load(self::RELATIONS))]);
    }

    public function update(UpdateTeacherAssignmentRequest $request, TeacherAssignment $teacherAssignment): JsonResponse
    {
        $record = $this->academicCrudService->update($teacherAssignment, $request->validated(), self::RELATIONS);

        return response()->json(['message' => 'Teacher assignment updated successfully.', 'data' => new TeacherAssignmentResource($record)]);
    }

    public function destroy(TeacherAssignment $teacherAssignment): JsonResponse
    {
        $this->academicCrudService->delete($teacherAssignment);

        return response()->json(['message' => 'Teacher assignment deleted successfully.']);
    }
}
PHP

python3 - <<'PY'
from pathlib import Path

path = Path('routes/api.php')
text = path.read_text()

text = text.replace("use App\\Http\\Controllers\\Api\\V1\\AcademicStructureController;\n", "")

imports = [
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\AcademicTermController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\AcademicYearController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\ClassArmController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\ClassLevelController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\SubjectController;",
    "use App\\Http\\Controllers\\Api\\V1\\Academics\\TeacherAssignmentController;",
]

for import_line in reversed(imports):
    if import_line not in text:
        text = text.replace("use Illuminate\\Support\\Facades\\Route;", import_line + "\nuse Illuminate\\Support\\Facades\\Route;")

replacements = {
    "[AcademicStructureController::class, 'academicYears']": "[AcademicYearController::class, 'index']",
    "[AcademicStructureController::class, 'storeAcademicYear']": "[AcademicYearController::class, 'store']",
    "[AcademicStructureController::class, 'showAcademicYear']": "[AcademicYearController::class, 'show']",
    "[AcademicStructureController::class, 'updateAcademicYear']": "[AcademicYearController::class, 'update']",
    "[AcademicStructureController::class, 'destroyAcademicYear']": "[AcademicYearController::class, 'destroy']",
    "[AcademicStructureController::class, 'academicTerms']": "[AcademicTermController::class, 'index']",
    "[AcademicStructureController::class, 'storeAcademicTerm']": "[AcademicTermController::class, 'store']",
    "[AcademicStructureController::class, 'showAcademicTerm']": "[AcademicTermController::class, 'show']",
    "[AcademicStructureController::class, 'updateAcademicTerm']": "[AcademicTermController::class, 'update']",
    "[AcademicStructureController::class, 'destroyAcademicTerm']": "[AcademicTermController::class, 'destroy']",
    "[AcademicStructureController::class, 'classLevels']": "[ClassLevelController::class, 'index']",
    "[AcademicStructureController::class, 'storeClassLevel']": "[ClassLevelController::class, 'store']",
    "[AcademicStructureController::class, 'showClassLevel']": "[ClassLevelController::class, 'show']",
    "[AcademicStructureController::class, 'updateClassLevel']": "[ClassLevelController::class, 'update']",
    "[AcademicStructureController::class, 'destroyClassLevel']": "[ClassLevelController::class, 'destroy']",
    "[AcademicStructureController::class, 'classArms']": "[ClassArmController::class, 'index']",
    "[AcademicStructureController::class, 'storeClassArm']": "[ClassArmController::class, 'store']",
    "[AcademicStructureController::class, 'showClassArm']": "[ClassArmController::class, 'show']",
    "[AcademicStructureController::class, 'updateClassArm']": "[ClassArmController::class, 'update']",
    "[AcademicStructureController::class, 'destroyClassArm']": "[ClassArmController::class, 'destroy']",
    "[AcademicStructureController::class, 'subjects']": "[SubjectController::class, 'index']",
    "[AcademicStructureController::class, 'storeSubject']": "[SubjectController::class, 'store']",
    "[AcademicStructureController::class, 'showSubject']": "[SubjectController::class, 'show']",
    "[AcademicStructureController::class, 'updateSubject']": "[SubjectController::class, 'update']",
    "[AcademicStructureController::class, 'destroySubject']": "[SubjectController::class, 'destroy']",
    "[AcademicStructureController::class, 'teacherAssignments']": "[TeacherAssignmentController::class, 'index']",
    "[AcademicStructureController::class, 'storeTeacherAssignment']": "[TeacherAssignmentController::class, 'store']",
    "[AcademicStructureController::class, 'showTeacherAssignment']": "[TeacherAssignmentController::class, 'show']",
    "[AcademicStructureController::class, 'updateTeacherAssignment']": "[TeacherAssignmentController::class, 'update']",
    "[AcademicStructureController::class, 'destroyTeacherAssignment']": "[TeacherAssignmentController::class, 'destroy']",
}

for old, new in replacements.items():
    text = text.replace(old, new)

path.write_text(text)
PY

echo "Running Pint..."
if [ -f "./vendor/bin/pint" ]; then
    ./vendor/bin/pint
else
    echo "Pint not found locally. Run: docker compose exec app ./vendor/bin/pint"
fi

echo "Done."
echo "Next:"
echo "docker compose exec app php artisan optimize:clear"
echo "docker compose exec app php artisan route:list"
echo "docker compose exec app php artisan test --filter=AcademicStructureModelsTest"
echo "docker compose exec app ./vendor/bin/pint --test"
