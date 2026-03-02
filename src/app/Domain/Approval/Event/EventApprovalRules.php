<?php

namespace App\Domain\Approval\Event;

use App\Domain\Approval\Contracts\ApprovalRules;
use App\Enums\ApprovalStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Access\AuthorizationException;
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
        // Only admin allowed to revert approved event
        return $actor->isActive() && $actor->isAdmin();
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

        if ($to === ApprovalStatus::PENDING) {

            if (! $model->title || ! $model->description) {
                throw new LogicException('Event content incomplete.');
            }

            if (! $model->registration_deadline) {
                throw new LogicException('Registration deadline is required.');
            }

            if ($model->registration_method === 'redirect' && ! $model->registration_url) {
                throw new LogicException('Redirect event must have registration URL.');
            }
        }

        if ($to === ApprovalStatus::APPROVED) {

            if ($model->registration_deadline?->isPast()) {
                throw new LogicException('Cannot approve event with past registration deadline.');
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

        $model->submitted_at = now();

        $model->rejected_at = null;
        $model->rejected_by = null;
        $model->rejection_reason = null;

        $model->is_active = false;
    }

    public function onApprove(Model $model, User $actor): void
    {
        /** @var Event $model */

        $model->approved_at = now();
        $model->approved_by = $actor->getKey();

        $this->autoPublish($model);
    }

    public function onReject(Model $model, User $actor, ?string $reason = null): void
    {
        /** @var Event $model */

        $model->rejected_at = now();
        $model->rejected_by = $actor->getKey();
        $model->rejection_reason = $reason;

        $model->is_active = false;
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
