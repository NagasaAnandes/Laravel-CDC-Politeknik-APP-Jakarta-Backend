<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case DRAFT     = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
        };
    }

    public function isSubmitted(): bool
    {
        return $this === self::SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
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
