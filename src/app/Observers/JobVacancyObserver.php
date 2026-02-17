<?php

namespace App\Observers;

use App\Models\JobVacancy;

class JobVacancyObserver
{
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
