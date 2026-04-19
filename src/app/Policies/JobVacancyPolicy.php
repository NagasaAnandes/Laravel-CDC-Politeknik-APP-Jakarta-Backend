<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;
use App\Enums\ApprovalStatus;

class JobVacancyPolicy
{
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function isCompanyOwner(User $user, JobVacancy $job): bool
    {
        return $user->role?->isCompany()
            && $job->company_id === $user->company_id;
    }

    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    public function viewAny(User $user): bool
    {
        // Internal listing (admin panel / partner panel)
        return $user->isActive()
            && ($user->isAdmin() || $user->role?->isCompany());
    }

    public function view(User $user, JobVacancy $job): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isCompanyOwner($user, $job);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->role?->isCompany());
    }

    /*
    |--------------------------------------------------------------------------
    | Update & Delete
    |--------------------------------------------------------------------------
    */

    public function update(User $user, JobVacancy $job): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isCompanyOwner($user, $job)
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
    | Workflow Actions
    |--------------------------------------------------------------------------
    | NOTE:
    | Policy = "who can"
    | Service = "is it valid"
    |--------------------------------------------------------------------------
    */

    public function submit(User $user, JobVacancy $job): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return in_array(
                $job->approval_status,
                [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                true
            );
        }

        return $this->isCompanyOwner($user, $job)
            && in_array(
                $job->approval_status,
                [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                true
            );
    }

    public function approve(User $user, JobVacancy $job): bool
    {
        return $user->isActive()
            && $user->isAdmin();
    }

    public function reject(User $user, JobVacancy $job): bool
    {
        return $this->approve($user, $job);
    }

    public function revert(User $user, JobVacancy $job): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if (! in_array(
            $job->approval_status,
            [ApprovalStatus::APPROVED, ApprovalStatus::REJECTED],
            true
        )) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $this->isCompanyOwner($user, $job);
    }

    public function cancel(User $user, JobVacancy $job): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return $job->approval_status === ApprovalStatus::APPROVED;
        }

        // optional: company boleh cancel job sendiri
        return $this->isCompanyOwner($user, $job)
            && $job->approval_status === ApprovalStatus::APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | Restore & Force Delete
    |--------------------------------------------------------------------------
    */

    public function restore(User $user, JobVacancy $job): bool
    {
        return false;
    }

    public function forceDelete(User $user, JobVacancy $job): bool
    {
        return false;
    }
}
