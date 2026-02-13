<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'priority',
        'target_audience',
        'redirect_url',
        'is_active',
        'published_at',
        'expired_at',
        'created_by',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expired_at')
                    ->orWhere('expired_at', '>=', now());
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Helpers
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if (! $this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expired_at && $this->expired_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isExpired(): bool
    {
        return $this->expired_at !== null
            && $this->expired_at->isPast();
    }

    public function isDraft(): bool
    {
        return ! $this->is_active || $this->published_at === null;
    }

    public function isScheduled(): bool
    {
        return $this->published_at !== null
            && $this->published_at->isFuture();
    }
}
