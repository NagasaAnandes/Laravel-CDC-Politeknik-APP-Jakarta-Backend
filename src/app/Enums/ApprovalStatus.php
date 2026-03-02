<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case DRAFT     = 'draft';
    case PENDING   = 'pending';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::PENDING   => 'Pending',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
        };
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}
