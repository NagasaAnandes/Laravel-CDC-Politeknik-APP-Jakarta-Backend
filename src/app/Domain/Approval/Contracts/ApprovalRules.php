<?php

namespace App\Domain\Approval\Contracts;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Enums\ApprovalStatus;

interface ApprovalRules
{
    /*
    |--------------------------------------------------------------------------
    | Authorization Layer
    |--------------------------------------------------------------------------
    */

    public function canSubmit(Model $model, User $actor): bool;

    public function canApprove(Model $model, User $actor): bool;

    public function canReject(
        Model $model,
        User $actor,
        ?string $reason = null
    ): bool;

    public function canRevert(Model $model, User $actor): bool;

    public function canCancel(Model $model, User $actor): bool;

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
    ): void;

    /*
    |--------------------------------------------------------------------------
    | State Mutation Hooks
    |--------------------------------------------------------------------------
    */

    public function onSubmit(Model $model, User $actor): void;

    public function onApprove(Model $model, User $actor): void;

    public function onReject(
        Model $model,
        User $actor,
        ?string $reason = null
    ): void;

    public function onRevert(Model $model, User $actor): void;

    public function onCancel(Model $model, User $actor): void;
}
