<?php

namespace App\Enums;

use DomainException;

enum EventStatus: string
{
    case DRAFT     = 'draft';
    case PENDING   = 'pending';
    case APPROVED  = 'approved';
    case REJECTED  = 'rejected';
    case CANCELLED = 'cancelled';

    /*
    |--------------------------------------------------------------------------
    | Transition Matrix
    |--------------------------------------------------------------------------
    */

    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::DRAFT => in_array($to, [
                self::PENDING,
            ], true),

            self::PENDING => in_array($to, [
                self::APPROVED,
                self::REJECTED,
            ], true),

            self::REJECTED => in_array($to, [
                self::PENDING,
            ], true),

            self::APPROVED => in_array($to, [
                self::CANCELLED,
            ], true),

            self::CANCELLED => false,
        };
    }

    public function transitionTo(self $to): self
    {
        if (! $this->canTransitionTo($to)) {
            throw new DomainException(
                "Invalid transition from {$this->value} to {$to->value}"
            );
        }

        return $to;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isFinal(): bool
    {
        return match ($this) {
            self::CANCELLED => true,
            default => false,
        };
    }

    public function isPublishable(): bool
    {
        return $this === self::APPROVED;
    }

    public function label(): string
    {
        return strtoupper($this->value);
    }
}
