<?php

namespace App\Services\Tenancy;

use App\Models\Campus;
use App\Models\School;
use App\Models\User;

class TenantContext
{
    private ?School $school = null;

    private ?Campus $campus = null;

    public function setSchool(?School $school): void
    {
        $this->school = $school;
    }

    public function school(): ?School
    {
        return $this->school;
    }

    public function schoolId(): ?string
    {
        return $this->school?->id;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }

    public function campus(): ?Campus
    {
        return $this->campus;
    }

    public function campusId(): ?string
    {
        return $this->campus?->id;
    }

    public function clear(): void
    {
        $this->school = null;
        $this->campus = null;
    }

    public function userBelongsToSchool(User $user, string $schoolId): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->schools()
            ->where('schools.id', $schoolId)
            ->exists();
    }

    public function requireSchoolId(): string
    {
        abort_if(
            ! $this->schoolId(),
            422,
            'No school context has been resolved for this request.'
        );

        return $this->schoolId();
    }

    public function requireCampusId(): string
    {
        abort_if(
            ! $this->campusId(),
            422,
            'No campus context has been resolved for this request.'
        );

        return $this->campusId();
    }
}