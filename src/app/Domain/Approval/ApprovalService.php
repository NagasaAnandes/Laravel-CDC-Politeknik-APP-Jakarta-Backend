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
    protected array $transitions = [
        ApprovalStatus::DRAFT->value => [
            ApprovalStatus::SUBMITTED->value,
        ],

        ApprovalStatus::SUBMITTED->value => [
            ApprovalStatus::APPROVED->value,
            ApprovalStatus::REJECTED->value,
        ],

        ApprovalStatus::REJECTED->value => [
            ApprovalStatus::DRAFT->value,
        ],

        ApprovalStatus::APPROVED->value => [
            ApprovalStatus::DRAFT->value,      // revert
            ApprovalStatus::CANCELLED->value,  // ✅ FIX: cancel
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | PUBLIC API
    |--------------------------------------------------------------------------
    */

    public function submit(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition($model, $actor, ApprovalStatus::SUBMITTED, 'submit', $rules);
    }

    public function approve(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition($model, $actor, ApprovalStatus::APPROVED, 'approve', $rules);
    }

    public function revert(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition($model, $actor, ApprovalStatus::DRAFT, 'revert', $rules);
    }

    public function cancel(Model $model, User $actor, ApprovalRules $rules): Model
    {
        return $this->transition($model, $actor, ApprovalStatus::CANCELLED, 'cancel', $rules);
    }

    public function reject(
        Model $model,
        User $actor,
        string $reason,
        ApprovalRules $rules
    ): Model {
        return $this->transition($model, $actor, ApprovalStatus::REJECTED, 'reject', $rules, $reason);
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

        return DB::transaction(function () use ($model, $actor, $to, $action, $rules, $reason) {

            $model = $this->lock($model);

            if (method_exists($model, 'trashed') && $model->trashed()) {
                throw new InvalidTransitionException('Cannot transition soft-deleted model.');
            }

            $from = $model->approval_status;

            if (! $from instanceof ApprovalStatus) {
                throw new \LogicException('approval_status must be casted to ApprovalStatus enum.');
            }

            // ✅ Idempotent
            if ($from === $to) {
                return $model;
            }

            // ❌ Engine-level transition guard
            if (! $this->canTransition($from, $to)) {
                throw new InvalidTransitionException(
                    "Invalid transition from {$from->value} to {$to->value}"
                );
            }

            // 🔐 Authorization
            if (! $this->authorize($action, $rules, $model, $actor, $reason)) {
                throw new AuthorizationException("Unauthorized action: {$action}");
            }

            // 🧠 Domain rules
            $rules->validateTransition($model, $actor, $from, $to);

            if (method_exists($model, 'bypassWorkflowGuard')) {
                $model->bypassWorkflowGuard();
            }

            $originalVersion = $model->version;

            $model->approval_status = $to;

            match ($action) {
                'submit'  => $rules->onSubmit($model, $actor),
                'approve' => $rules->onApprove($model, $actor),
                'reject'  => $rules->onReject($model, $actor, $reason),
                'revert'  => $rules->onRevert($model, $actor),
                'cancel'  => $rules->onCancel($model, $actor),
                default   => throw new \LogicException("Unknown action: {$action}")
            };

            // 🔥 Optimistic Locking
            $updated = $model->newQuery()
                ->whereKey($model->getKey())
                ->where('version', $originalVersion)
                ->update(array_merge(
                    $model->getAttributes(),
                    ['version' => $originalVersion + 1]
                ));

            if (! $updated) {
                throw new \RuntimeException('Race condition detected. Please retry.');
            }

            $model->refresh();

            DB::afterCommit(function () use ($model, $from, $to, $action, $actor, $reason) {
                $this->log($model, $from, $to, $action, $actor, $reason);
            });

            return $model;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | INTERNAL
    |--------------------------------------------------------------------------
    */

    protected function lock(Model $model): Model
    {
        return $model::query()
            ->whereKey($model->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    protected function canTransition(ApprovalStatus $from, ApprovalStatus $to): bool
    {
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
            'cancel'  => $rules->canCancel($model, $actor), // ✅ FIX
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
