<?php

namespace App\Enums;

enum JobApplicationStatus: string
{
    case PENDING = 'pending';
    case REVIEWED = 'reviewed';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::REVIEWED => 'Reviewed',
            self::ACCEPTED => 'Accepted',
            self::REJECTED => 'Rejected',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::ACCEPTED, self::REJECTED], true);
    }

    public static function options(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::REVIEWED->value => self::REVIEWED->label(),
            self::ACCEPTED->value => self::ACCEPTED->label(),
            self::REJECTED->value => self::REJECTED->label(),
        ];
    }

    public function nextTransitions(): array
    {
        return match ($this) {
            self::PENDING => [self::REVIEWED],
            self::REVIEWED => [self::ACCEPTED, self::REJECTED],
            self::ACCEPTED, self::REJECTED => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->nextTransitions(), true);
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'border-amber-200 bg-amber-50 text-amber-800 dark:border-amber-900 dark:bg-amber-950/40 dark:text-amber-200',
            self::REVIEWED => 'border-sky-200 bg-sky-50 text-sky-800 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-200',
            self::ACCEPTED => 'border-emerald-200 bg-emerald-50 text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-200',
            self::REJECTED => 'border-rose-200 bg-rose-50 text-rose-800 dark:border-rose-900 dark:bg-rose-950/40 dark:text-rose-200',
        };
    }

    public function dashboardColor(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::REVIEWED => 'info',
            self::ACCEPTED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
