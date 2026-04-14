<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Enums\ApprovalStatus;

class EventPolicy
{
    /*
    |--------------------------------------------------------------------------
    | LISTING
    |--------------------------------------------------------------------------
    */

    public function viewAny(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->role?->isCompany());
    }

    /*
    |--------------------------------------------------------------------------
    | VIEW
    |--------------------------------------------------------------------------
    */

    public function view(User $user, Event $event): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role?->isCompany()) {
            return $event->company_id === $user->company_id;
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(User $user): bool
    {
        return $user->isActive()
            && ($user->isAdmin() || $user->role?->isCompany());
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(User $user, Event $event): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        // Admin boleh update semua kecuali workflow fields (guarded model)
        if ($user->isAdmin()) {
            return true;
        }

        // Company hanya boleh update event miliknya
        if ($user->role?->isCompany()) {

            if ($event->company_id !== $user->company_id) {
                return false;
            }

            // Company tidak boleh edit setelah approved
            return in_array(
                $event->approval_status,
                [
                    ApprovalStatus::DRAFT,
                    ApprovalStatus::REJECTED,
                ],
                true
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE (Soft Delete Only)
    |--------------------------------------------------------------------------
    */

    public function delete(User $user, Event $event): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role?->isCompany()) {

            if ($event->company_id !== $user->company_id) {
                return false;
            }

            // Company hanya boleh delete sebelum approved
            return in_array(
                $event->approval_status,
                [
                    ApprovalStatus::DRAFT,
                    ApprovalStatus::REJECTED,
                ],
                true
            );
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVAL ACTIONS (Admin Only)
    |--------------------------------------------------------------------------
    */

    public function approve(User $user, Event $event): bool
    {
        return $user->isActive()
            && $user->isAdmin()
            && $event->approval_status === ApprovalStatus::SUBMITTED;
    }

    public function reject(User $user, Event $event): bool
    {
        return $user->isActive()
            && $user->isAdmin()
            && $event->approval_status === ApprovalStatus::SUBMITTED;
    }

    public function revert(User $user, Event $event): bool
    {
        return $user->isActive()
            && $user->isAdmin()
            && $event->approval_status === ApprovalStatus::APPROVED;
    }

    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    public function submit(User $user, Event $event): bool
    {
        if (! $user->isActive()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->role?->isCompany()) {
            return $event->company_id === $user->company_id
                && in_array(
                    $event->approval_status,
                    [
                        ApprovalStatus::DRAFT,
                        ApprovalStatus::REJECTED,
                    ],
                    true
                );
        }

        return false;
    }



    /*
    |--------------------------------------------------------------------------
    | RESTORE / FORCE DELETE
    |--------------------------------------------------------------------------
    */

    public function restore(User $user, Event $event): bool
    {
        return false;
    }

    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }
}
