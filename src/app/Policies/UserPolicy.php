<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->role->isAdmin();
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->role->isAdmin();
    }

    public function create(User $actor): bool
    {
        return $actor->role->isAdmin();
    }

    public function update(User $actor, User $target): bool
    {
        if (! $actor->role->isAdmin()) {
            return false;
        }

        // Admin biasa tidak boleh edit super admin
        if (
            $actor->role !== UserRole::SUPER_ADMIN &&
            $target->role === UserRole::SUPER_ADMIN
        ) {
            return false;
        }

        return true;
    }

    public function delete(User $actor, User $target): bool
    {
        if (! $actor->role->isAdmin()) {
            return false;
        }

        // Tidak bisa delete diri sendiri
        if ($actor->id === $target->id) {
            return false;
        }

        // Tidak bisa delete super admin kalau bukan super admin
        if (
            $actor->role !== UserRole::SUPER_ADMIN &&
            $target->role === UserRole::SUPER_ADMIN
        ) {
            return false;
        }

        // Tidak bisa delete last super admin
        if (
            $target->role === UserRole::SUPER_ADMIN &&
            User::where('role', UserRole::SUPER_ADMIN)->count() <= 1
        ) {
            return false;
        }

        return true;
    }
}
