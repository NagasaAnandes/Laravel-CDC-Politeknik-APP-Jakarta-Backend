<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case DRAFT     = 'draft';
    case SUBMITTED = 'submitted';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT     => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::APPROVED  => 'Approved',
            self::REJECTED  => 'Rejected',
            self::CANCELLED => 'Cancelled'
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
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
    public function canBeEdited(): bool
    {
        return in_array($this, [
            self::DRAFT,
            self::REJECTED,
        ], true);
    }
    public function isFinal(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::REJECTED,
            self::CANCELLED,
        ], true);
    }
    public function isActiveState(): bool
    {
        return $this === self::APPROVED;
    }
}
