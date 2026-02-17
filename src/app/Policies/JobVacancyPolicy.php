<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;
use App\Enums\ApprovalStatus;
use Illuminate\Auth\Access\Response;

class JobVacancyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, JobVacancy $jobVacancy): bool
    {
        if ($user->role->isAdmin()) {
            return true;
        }

        // Public visibility handled elsewhere
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, JobVacancy $jobVacancy): bool
    {
        return $user->role->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, JobVacancy $jobVacancy): bool
    {
        return $user->role->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, JobVacancy $jobVacancy): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, JobVacancy $jobVacancy): bool
    {
        return false;
    }

    /**
     * Approval actions
     */
    public function approve(User $user, JobVacancy $job): bool
    {
        return $user->role->isAdmin()
            && $job->approval_status === ApprovalStatus::PENDING;
    }

    public function reject(User $user, JobVacancy $job): bool
    {
        return $user->role->isAdmin()
            && $job->approval_status === ApprovalStatus::PENDING;
    }

    public function submit(User $user, JobVacancy $job): bool
    {
        return $user->role->isAdmin()
            && $job->approval_status === ApprovalStatus::DRAFT;
    }
}
