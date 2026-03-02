<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;
use App\Enums\ApprovalStatus;

class JobVacancyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin()
            || $user->role?->isCompany();
    }

    public function view(User $user, JobVacancy $job): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->role?->isCompany()
            && $job->company_id === $user->company_id;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin()
            || $user->role?->isCompany();
    }

    public function update(User $user, JobVacancy $job): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->role?->isCompany()
            && $job->company_id === $user->company_id
            && in_array(
                $job->approval_status,
                [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                true
            );
    }

    public function delete(User $user, JobVacancy $job): bool
    {
        return $this->update($user, $job);
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow Actions (Role Only)
    |--------------------------------------------------------------------------
    | Transition validity is checked in ApprovalService
    */

    public function approve(User $user, JobVacancy $job): bool
    {
        return $user->isAdmin();
    }

    public function reject(User $user, JobVacancy $job): bool
    {
        return $user->isAdmin();
    }

    public function submit(User $user, JobVacancy $job): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->role?->isCompany()
            && $job->company_id === $user->company_id;
    }

    public function restore(User $user, JobVacancy $job): bool
    {
        return false;
    }

    public function forceDelete(User $user, JobVacancy $job): bool
    {
        return false;
    }
}
