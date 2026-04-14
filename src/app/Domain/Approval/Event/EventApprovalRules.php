<?php

namespace App\Domain\Approval\Event;

use App\Domain\Approval\Contracts\ApprovalRules;
use App\Enums\ApprovalStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class EventApprovalRules implements ApprovalRules
{
    /*
    |--------------------------------------------------------------------------
    | AUTHORIZATION
    |--------------------------------------------------------------------------
    */

    public function canSubmit(Model $model, User $actor): bool
    {
        /** @var Event $model */

        if ($actor->isAdmin()) {
            return true;
        }

        return $actor->isActive()
            && $actor->role?->isCompany()
            && $model->company_id === $actor->company_id;
    }

    public function canApprove(Model $model, User $actor): bool
    {
        return $actor->isActive() && $actor->isAdmin();
    }

    public function canReject(Model $model, User $actor, ?string $reason = null): bool
    {
        return $actor->isActive() && $actor->isAdmin();
    }

    public function canRevert(Model $model, User $actor): bool
    {
        /** @var Event $model */

        return $actor->isActive()
            && $actor->isAdmin()
            && $model->approval_status === ApprovalStatus::APPROVED
            && $model->registrations_count === 0; // 🔒 guard utama
    }

    /*
    |--------------------------------------------------------------------------
    | DOMAIN INVARIANT VALIDATION
    |--------------------------------------------------------------------------
    */

    public function validateTransition(
        Model $model,
        User $actor,
        ApprovalStatus $from,
        ApprovalStatus $to
    ): void {

        /** @var Event $model */

        /*
        |--------------------------------------------------------------------------
        | BASIC SANITY CHECK
        |--------------------------------------------------------------------------
        */

        if (! in_array($model->registration_method, ['internal', 'redirect'], true)) {
            throw new LogicException('Invalid registration method.');
        }

        /*
        |--------------------------------------------------------------------------
        | SUBMIT VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($to === ApprovalStatus::SUBMITTED) {

            if (! $model->title || ! $model->description) {
                throw new LogicException('Event content incomplete.');
            }

            if (! $model->registration_deadline) {
                throw new LogicException('Registration deadline is required.');
            }

            if ($model->registration_method === 'redirect' && ! $model->registration_url) {
                throw new LogicException('Redirect event must have registration URL.');
            }

            if ($model->registration_method === 'internal' && $model->registration_url) {
                throw new LogicException('Internal event should not have registration URL.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | APPROVE VALIDATION
        |--------------------------------------------------------------------------
        */

        if ($to === ApprovalStatus::APPROVED) {

            if ($model->registration_deadline <= now()) {
                throw new LogicException('Cannot approve expired event.');
            }

            if (! $model->title || ! $model->description) {
                throw new LogicException('Event data corrupted before approval.');
            }
        }

        /*
        |--------------------------------------------------------------------------
        | REVERT VALIDATION (CRITICAL)
        |--------------------------------------------------------------------------
        */

        if (
            $from === ApprovalStatus::APPROVED &&
            $to === ApprovalStatus::DRAFT
        ) {
            if ($model->registrations_count > 0) {
                throw new LogicException('Cannot revert event with existing registrations.');
            }

            if ($model->registration_deadline <= now()) {
                throw new LogicException('Cannot revert expired event.');
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | STATE MUTATION
    |--------------------------------------------------------------------------
    */

    public function onSubmit(Model $model, User $actor): void
    {
        /** @var Event $model */

        if ($model->approval_status === ApprovalStatus::SUBMITTED) {
            return; // idempotent
        }

        $model->submitted_at = now();

        $model->approved_at = null;
        $model->approved_by = null;

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        $model->cancelled_at = null;
        $model->cancelled_by = null;

        $model->is_active = false;
        $model->published_at = null;
    }

    public function onApprove(Model $model, User $actor): void
    {
        /** @var Event $model */

        $model->approved_at = now();
        $model->approved_by = $actor->getKey();

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        $model->cancelled_at = null;
        $model->cancelled_by = null;

        $this->autoPublish($model);
    }

    public function onReject(Model $model, User $actor, ?string $reason = null): void
    {
        /** @var Event $model */

        $model->rejected_at = now();
        $model->rejected_by = $actor->getKey();
        $model->rejection_reason = $reason;

        $model->approved_at = null;
        $model->approved_by = null;

        $model->is_active = false;
        $model->published_at = null;
    }

    public function onRevert(Model $model, User $actor): void
    {
        /** @var Event $model */

        $model->submitted_at = null;

        $model->approved_at = null;
        $model->approved_by = null;

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        $model->cancelled_at = null;
        $model->cancelled_by = null;

        $model->is_active = false;
        $model->published_at = null;
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL
    |--------------------------------------------------------------------------
    */

    protected function autoPublish(Event $event): void
    {
        $event->is_active = true;
        $event->published_at ??= now();
    }
}
