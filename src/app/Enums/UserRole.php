<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN_CDC   = 'admin_cdc';
    case STUDENT     = 'student';
    case ALUMNI      = 'alumni';
    case COMPANY     = 'company';

    public function isAdmin(): bool
    {
        return in_array($this, [
            self::SUPER_ADMIN,
            self::ADMIN_CDC,
        ], true);
    }
}
