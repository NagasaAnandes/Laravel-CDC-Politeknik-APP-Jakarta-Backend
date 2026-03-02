<?php

namespace App\Domain\Approval\Job;

use App\Domain\Approval\Contracts\ApprovalRules;
use App\Enums\ApprovalStatus;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JobApprovalRules implements ApprovalRules
{
    /*
    |--------------------------------------------------------------------------
    | Authorization
    |--------------------------------------------------------------------------
    */

    public function canSubmit(Model $model, User $actor): bool
    {
        /** @var JobVacancy $model */

        if (! $actor->isActive()) {
            return false;
        }

        if ($actor->isAdmin()) {
            return in_array(
                $model->approval_status,
                [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                true
            );
        }

        if ($actor->role?->isCompany()) {
            return $model->company_id === $actor->company_id
                && in_array(
                    $model->approval_status,
                    [ApprovalStatus::DRAFT, ApprovalStatus::REJECTED],
                    true
                );
        }

        return false;
    }

    public function canApprove(Model $model, User $actor): bool
    {
        /** @var JobVacancy $model */

        return $actor->isActive()
            && $actor->isAdmin()
            && $model->approval_status === ApprovalStatus::PENDING;
    }

    public function canReject(
        Model $model,
        User $actor,
        ?string $reason = null
    ): bool {
        return $this->canApprove($model, $actor);
    }

    public function canRevert(Model $model, User $actor): bool
    {
        /** @var JobVacancy $model */

        if (! $actor->isActive()) {
            return false;
        }

        // Revert hanya boleh dari APPROVED atau REJECTED
        if (! in_array(
            $model->approval_status,
            [ApprovalStatus::APPROVED, ApprovalStatus::REJECTED],
            true
        )) {
            return false;
        }

        // Admin selalu boleh revert
        if ($actor->isAdmin()) {
            return true;
        }

        // Optional: company boleh revert miliknya sendiri
        if ($actor->role?->isCompany()) {
            return $model->company_id === $actor->company_id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | Domain Invariant Validation
    |--------------------------------------------------------------------------
    */

    public function validateTransition(
        Model $model,
        User $actor,
        ApprovalStatus $from,
        ApprovalStatus $to
    ): void {
        /** @var JobVacancy $model */

        // Submit invariant
        if ($to === ApprovalStatus::PENDING) {
            if (empty($model->external_apply_url)) {
                throw new \LogicException(
                    'Job must have external apply URL before submission.'
                );
            }
        }

        // Approve invariant
        if ($to === ApprovalStatus::APPROVED) {
            if ($model->expired_at && $model->expired_at->isPast()) {
                // Notice: we DO NOT block approval.
                // We just allow approve but it won't publish.
                return;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Hooks
    |--------------------------------------------------------------------------
    */

    public function onSubmit(Model $model, User $actor): void
    {
        /** @var JobVacancy $model */

        $model->submitted_at = now();

        $model->approved_at = null;
        $model->approved_by = null;

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        $model->is_active = false;
    }

    public function onApprove(Model $model, User $actor): void
    {
        /** @var JobVacancy $model */

        $model->approved_at = now();
        $model->approved_by = $actor->getKey();

        // Publication decision
        if ($model->expired_at && $model->expired_at->isPast()) {
            // Approved but not visible
            $model->is_active = false;
            return;
        }

        $model->is_active = true;
        $model->published_at ??= now();
    }

    public function onReject(
        Model $model,
        User $actor,
        ?string $reason = null
    ): void {
        /** @var JobVacancy $model */

        if (Str::of((string) $reason)->trim()->isEmpty()) {
            throw new \InvalidArgumentException(
                'Rejection reason is required.'
            );
        }

        $model->rejected_at = now();
        $model->rejected_by = $actor->getKey();
        $model->rejection_reason = $reason;

        $model->is_active = false;
    }

    public function onRevert(Model $model, User $actor): void
    {
        /** @var JobVacancy $model */

        // Reset workflow metadata
        $model->submitted_at = null;

        $model->approved_at = null;
        $model->approved_by = null;

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        // Revert always makes it inactive
        $model->is_active = false;

        // Optional: reset publication
        $model->published_at = null;
    }
}
