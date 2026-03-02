<?php

namespace App\Observers;

use App\Models\JobVacancy;
use App\Enums\ApprovalStatus;
use Illuminate\Support\Facades\Auth;

class JobVacancyObserver
{
    /**
     * Fields that trigger approval reset when modified.
     */
    protected array $materialFields = [
        'title',
        'company_name',
        'location',
        'employment_type',
        'description',
        'external_apply_url',
        'poster_path',
        'published_at',
        'expired_at',
    ];

    public function updating(JobVacancy $job): void
    {
        // Only care if previously APPROVED
        if ($job->getOriginal('approval_status') !== ApprovalStatus::APPROVED->value) {
            return;
        }

        // No auth context (CLI / queue)
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Admin bypass
        if ($user->role->isAdmin()) {
            return;
        }

        // Check if any material field changed
        foreach ($this->materialFields as $field) {
            if ($job->isDirty($field)) {
                $this->resetApproval($job);
                break;
            }
        }
    }

    protected function resetApproval(JobVacancy $job): void
    {
        $job->approval_status = ApprovalStatus::PENDING;
        $job->approved_at = null;
        $job->approved_by = null;
        $job->rejected_at = null;
        $job->rejected_by = null;
        $job->rejection_reason = null;
    }

    /**
     * Handle the JobVacancy "created" event.
     */
    public function created(JobVacancy $jobVacancy): void
    {
        //
    }

    /**
     * Handle the JobVacancy "updated" event.
     */
    public function updated(JobVacancy $jobVacancy): void
    {
        //
    }

    /**
     * Handle the JobVacancy "deleted" event.
     */
    public function deleted(JobVacancy $jobVacancy): void
    {
        //
    }

    /**
     * Handle the JobVacancy "restored" event.
     */
    public function restored(JobVacancy $jobVacancy): void
    {
        //
    }

    /**
     * Handle the JobVacancy "force deleted" event.
     */
    public function forceDeleted(JobVacancy $jobVacancy): void
    {
        //
    }
}
