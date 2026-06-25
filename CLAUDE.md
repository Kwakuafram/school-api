# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run all tests
composer test
# or: php artisan test

# Run a single test file
php artisan test tests/Feature/Academics/AcademicStructureApiTest.php

# Format code (auto-fix)
./vendor/bin/pint

# Check formatting without modifying (CI mode)
./vendor/bin/pint --test

# Start dev server with queue worker and log viewer
composer run dev

# Full initial setup (install deps, migrate, build assets)
composer setup

# Run migrations and seeders
php artisan migrate
php artisan db:seed
```

Docker runs the full stack (API on `:8000`, PGAdmin on `:5050`, Mailpit on `:8025`):
```bash
docker-compose up -d
```

## Architecture Overview

This is a **multi-tenant school management REST API** built on Laravel 13 (PHP 8.3+) with PostgreSQL and Redis.

### Multi-Tenancy

Tenancy is custom header-based, NOT a third-party tenancy package. The `ResolveTenant` middleware (`app/Http/Middleware/ResolveTenant.php`) reads two headers:

- `X-School-ID` (required) — UUID of the school
- `X-Campus-ID` (optional) — UUID of a campus within that school

The resolved tenant is stored in the `TenantContext` service (`app/Services/Tenancy/TenantContext.php`). All tenant-scoped models carry a `school_id` FK enforced through the `BelongsToSchool` trait. The `super_admin` role bypasses tenant membership checks.

Routes under the `middleware('tenant')` group require these headers.

### Authentication & Authorization

- **Auth**: Laravel Sanctum — Bearer tokens returned from `POST /api/v1/auth/login`
- **Permissions**: Spatie Laravel Permission — roles (e.g. `school_admin`, `teacher`) and granular permissions (e.g. `academics.view`, `students.create`) gated per route with `permission:` middleware
- Every protected route requires both `auth:sanctum` and the `tenant` middleware (except auth and platform-level school routes)

### API Versioning

URL-based: all routes are prefixed `/api/v1/`. Routes are defined in `routes/api.php`.

### Domain Model

**Platform level** (no tenant scope):
- `School` — top-level organization entity
- `User` — authenticatable; belongs to schools via a pivot with `role_context` and `is_default`

**Tenant level** (scoped by `school_id`, some also by `campus_id`):
- `Campus` — sub-unit of a school; all academic/student data belongs here
- `AcademicYear` / `AcademicTerm` — define the academic calendar; only one can be `is_current` at a time
- `ClassLevel` — grade/form (e.g. "Grade 7"); has many `ClassArm` (sections)
- `ClassArm` — section within a class level, tied to a campus
- `Subject` — course/subject offered by the school
- `TeacherAssignment` — links a teacher (`User`) to a class arm and subject for a given academic period
- `Student` — carries a `current_enrollment_id` FK pointing to their active `StudentEnrollment`
- `StudentEnrollment` — records a student's placement in a class for an academic period
- `Guardian` — linked to students via a rich pivot (relationship type, pickup permission, communication prefs)
- `AuditLog` — polymorphic event log; auto-populated by the `RecordsAuditLogs` trait

### Custom Model Traits (`app/Models/Traits/`)

| Trait | Purpose |
|---|---|
| `HasUuidPrimaryKey` | Auto-generates UUID on `creating` |
| `BelongsToSchool` | Adds `school()` relationship |
| `RecordsAuditLogs` | Writes to `audit_logs` on create/update/delete |

### Request / Response Layer

- **Form Requests** (`app/Http/Requests/`) — all validation lives here, not in controllers
- **API Resources** (`app/Http/Resources/`) — control JSON shape; controllers return these, not raw models
- **Services** (`app/Services/`) — business logic extracted from controllers (Tenancy, Academics, Students, etc.)

### Testing Conventions

Tests use `RefreshDatabase` and SQLite in-memory (configured in `phpunit.xml`). The pattern for Feature tests:

1. Create a school, campus, and user with `school_user` pivot
2. Assign roles/permissions (seed via `AcademicPermissionSeeder` or equivalent)
3. Authenticate with `Sanctum::actingAs($user)`
4. Attach `X-School-ID` (and optionally `X-Campus-ID`) headers — many test classes have a `withTenant()` helper for this

### CI Pipeline

`.github/workflows/backend-ci.yml` runs on push/PR to `develop` and `main`: installs deps, runs migrations against a PostgreSQL 16 test DB, then runs `php artisan test` and `./vendor/bin/pint --test`. Both must pass.