<?php

namespace App\Domain\Approval;

use App\Domain\Approval\Contracts\ApprovalRules;
use App\Domain\Approval\Exceptions\InvalidTransitionException;
use App\Enums\ApprovalStatus;
use App\Models\ApprovalLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ApprovalService
{
    /**
     * Explicit transition matrix.
     * Single source of truth.
     */
    protected array $transitions = [
        ApprovalStatus::DRAFT->value => [
            ApprovalStatus::PENDING->value,
        ],
        ApprovalStatus::PENDING->value => [
            ApprovalStatus::APPROVED->value,
            ApprovalStatus::REJECTED->value,
        ],
        ApprovalStatus::REJECTED->value => [
            ApprovalStatus::DRAFT->value,
        ],
        ApprovalStatus::APPROVED->value => [
            ApprovalStatus::DRAFT->value,
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | PUBLIC API
    |--------------------------------------------------------------------------
    */

    public function submit(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition(
            model: $model,
            actor: $actor,
            to: ApprovalStatus::PENDING,
            action: 'submit',
            rules: $rules
        );
    }

    public function approve(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition(
            model: $model,
            actor: $actor,
            to: ApprovalStatus::APPROVED,
            action: 'approve',
            rules: $rules
        );
    }

    public function revert(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition(
            model: $model,
            actor: $actor,
            to: ApprovalStatus::DRAFT,
            action: 'revert',
            rules: $rules
        );
    }

    public function reject(
        Model $model,
        User $actor,
        string $reason,
        ApprovalRules $rules
    ): Model {
        return $this->transition(
            model: $model,
            actor: $actor,
            to: ApprovalStatus::REJECTED,
            action: 'reject',
            rules: $rules,
            reason: $reason
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CORE ENGINE
    |--------------------------------------------------------------------------
    */

    protected function transition(
        Model $model,
        User $actor,
        ApprovalStatus $to,
        string $action,
        ApprovalRules $rules,
        ?string $reason = null
    ): Model {

        return DB::transaction(function () use (
            $model,
            $actor,
            $to,
            $action,
            $rules,
            $reason
        ) {

            // Lock fresh model instance
            $model = $this->lock($model);

            // Soft delete protection
            if (method_exists($model, 'trashed') && $model->trashed()) {
                throw new InvalidTransitionException('Cannot transition soft-deleted model.');
            }

            $from = $model->approval_status;

            if (! $from instanceof ApprovalStatus) {
                throw new \LogicException('approval_status must be casted to ApprovalStatus enum.');
            }

            // Validate transition via explicit matrix
            if (! $this->canTransition($from, $to)) {
                throw new InvalidTransitionException(
                    "Invalid transition from {$from->value} to {$to->value}"
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Authorization (Explicit Boolean Contract)
            |--------------------------------------------------------------------------
            */

            if (! $this->authorize($action, $rules, $model, $actor, $reason)) {
                throw new AuthorizationException("Unauthorized action: {$action}");
            }

            /*
            |--------------------------------------------------------------------------
            | Pre-transition Hook (Domain Invariant Validation)
            |--------------------------------------------------------------------------
            */
            $rules->validateTransition($model, $actor, $from, $to);

            /*
            |--------------------------------------------------------------------------
            | Apply State
            |--------------------------------------------------------------------------
            */

            if (method_exists($model, 'bypassWorkflowGuard')) {
                $model->bypassWorkflowGuard();
            }

            $model->approval_status = $to;

            match ($action) {
                'submit'  => $rules->onSubmit($model, $actor),
                'approve' => $rules->onApprove($model, $actor),
                'reject'  => $rules->onReject($model, $actor, $reason),
                'revert'  => $rules->onRevert($model, $actor),
            };

            $model->save();

            /*
            |--------------------------------------------------------------------------
            | Lifecycle Hooks
            |--------------------------------------------------------------------------
            */

            /*
            |--------------------------------------------------------------------------
            | Logging (Immutable Audit Trail)
            |--------------------------------------------------------------------------
            */

            $this->log($model, $from, $to, $action, $actor, $reason);

            /*
            |--------------------------------------------------------------------------
            | Post-transition Hook
            |--------------------------------------------------------------------------
            */

            return $model;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL METHODS
    |--------------------------------------------------------------------------
    */

    protected function lock(Model $model): Model
    {
        return $model::query()
            ->whereKey($model->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function canTransition(
        ApprovalStatus $from,
        ApprovalStatus $to
    ): bool {
        return in_array(
            $to->value,
            $this->transitions[$from->value] ?? [],
            true
        );
    }

    protected function authorize(
        string $action,
        ApprovalRules $rules,
        Model $model,
        User $actor,
        ?string $reason = null
    ): bool {

        return match ($action) {
            'submit'  => $rules->canSubmit($model, $actor),
            'approve' => $rules->canApprove($model, $actor),
            'reject'  => $rules->canReject($model, $actor, $reason),
            'revert'  => $rules->canRevert($model, $actor),
            default   => false,
        };
    }

    protected function log(
        Model $model,
        ApprovalStatus $from,
        ApprovalStatus $to,
        string $action,
        User $actor,
        ?string $reason = null
    ): void {

        ApprovalLog::create([
            'approvable_type' => $model->getMorphClass(),
            'approvable_id'   => $model->getKey(),
            'from_status'     => $from->value,
            'to_status'       => $to->value,
            'action'          => $action,
            'performed_by'    => $actor->getKey(),
            'reason'          => $reason,
        ]);
    }
}
