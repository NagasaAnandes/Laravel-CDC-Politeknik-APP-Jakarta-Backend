<?php

namespace App\Enums;

enum ApprovalStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function canSubmit(): bool
    {
        return $this === self::DRAFT;
    }

    public function canApprove(): bool
    {
        return $this === self::PENDING;
    }

    public function canReject(): bool
    {
        return $this === self::PENDING;
    }

    public function canResubmit(): bool
    {
        return $this === self::REJECTED;
    }

    public function isFinal(): bool
    {
        return in_array($this, [
            self::APPROVED,
            self::REJECTED,
        ], true);
    }

    public function label(): string
    {
        return strtoupper($this->value);
    }
}
