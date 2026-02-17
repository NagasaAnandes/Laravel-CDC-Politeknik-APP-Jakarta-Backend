<?php

namespace App\Domain\Approval;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalLog;
use App\Models\JobVacancy;
use App\Models\User;
use App\Domain\Approval\Exceptions\InvalidTransitionException;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    public function submit(JobVacancy $job, User $actor): JobVacancy
    {
        return DB::transaction(function () use ($job, $actor) {

            if (! $job->approval_status->canSubmit()) {
                throw new InvalidTransitionException('Only draft can be submitted.');
            }

            $from = $job->approval_status;

            $job->approval_status = ApprovalStatus::PENDING;
            $job->submitted_at = now();
            $job->rejected_at = null;
            $job->rejected_by = null;
            $job->rejection_reason = null;

            $job->save();

            $this->log($job, $from, ApprovalStatus::PENDING, 'submit', $actor);

            return $job;
        });
    }

    public function approve(JobVacancy $job, User $admin): JobVacancy
    {
        return DB::transaction(function () use ($job, $admin) {

            if (! $job->approval_status->canApprove()) {
                throw new InvalidTransitionException('Only pending jobs can be approved.');
            }

            $from = $job->approval_status;

            $job->approval_status = ApprovalStatus::APPROVED;
            $job->approved_at = now();
            $job->approved_by = $admin->id;

            if (! $job->published_at) {
                $job->published_at = now();
            }

            $job->save();

            $this->log($job, $from, ApprovalStatus::APPROVED, 'approve', $admin);

            return $job;
        });
    }

    public function reject(JobVacancy $job, User $admin, string $reason): JobVacancy
    {
        return DB::transaction(function () use ($job, $admin, $reason) {

            if (! $job->approval_status->canReject()) {
                throw new InvalidTransitionException('Only pending jobs can be rejected.');
            }

            $from = $job->approval_status;

            $job->approval_status = ApprovalStatus::REJECTED;
            $job->rejected_at = now();
            $job->rejected_by = $admin->id;
            $job->rejection_reason = $reason;

            $job->save();

            $this->log($job, $from, ApprovalStatus::REJECTED, 'reject', $admin, $reason);

            return $job;
        });
    }

    public function resubmit(JobVacancy $job, User $actor): JobVacancy
    {
        return DB::transaction(function () use ($job, $actor) {

            if (! $job->approval_status->canResubmit()) {
                throw new InvalidTransitionException('Only rejected jobs can be resubmitted.');
            }

            $from = $job->approval_status;

            $job->approval_status = ApprovalStatus::PENDING;
            $job->submitted_at = now();
            $job->rejected_at = null;
            $job->rejected_by = null;
            $job->rejection_reason = null;

            $job->save();

            $this->log($job, $from, ApprovalStatus::PENDING, 'resubmit', $actor);

            return $job;
        });
    }

    protected function log(
        JobVacancy $job,
        ApprovalStatus $from,
        ApprovalStatus $to,
        string $action,
        User $actor,
        ?string $reason = null
    ): void {
        ApprovalLog::create([
            'approvable_type' => $job->getMorphClass(),
            'approvable_id' => $job->id,
            'from_status' => $from->value,
            'to_status' => $to->value,
            'action' => $action,
            'performed_by' => $actor->id,
            'reason' => $reason,
        ]);
    }
}
