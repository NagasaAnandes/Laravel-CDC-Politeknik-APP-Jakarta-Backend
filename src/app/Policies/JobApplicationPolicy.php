<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobApplication;
use App\Models\User;

class JobApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->isCompany() || $user->isStudentOrAlumni());
    }

    public function view(User $user, JobApplication $application): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($application->user_id === $user->id) {
            return true;
        }

        return $user->isCompany()
            && $application->jobVacancy?->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isActive()
            && in_array($user->role, [UserRole::STUDENT, UserRole::ALUMNI], true);
    }

    public function update(User $user, JobApplication $application): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isCompany()
            && $application->jobVacancy?->company_id === $user->company_id;
    }

    /**
     * Determine whether the user can view internal recruiter notes for the application.
     * By default only admins may view internal notes.
     */
    public function viewInternalNote(User $user, JobApplication $application): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        return $user->isAdmin();
    }
}
