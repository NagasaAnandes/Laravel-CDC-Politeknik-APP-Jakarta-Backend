<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class JobVacancy extends Model
{
    use HasFactory, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment Protection
    |--------------------------------------------------------------------------
    */

    protected $guarded = [
        // Workflow fields (controlled by ApprovalService)
        'approval_status',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',

        // Publication fields (controlled by Service)
        'is_active',
        'published_at',

        // Concurrency control
        'version',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'approval_status' => ApprovalStatus::class,

        'is_active'    => 'boolean',
        'published_at' => 'datetime',
        'expired_at'   => 'datetime',

        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',

        // 🔥 CRITICAL FIX (optimistic locking)
        'version' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    protected $attributes = [
        'approval_status' => 'draft',
        'is_active'       => false,
        'version'         => 0,
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Guard (Domain Integrity)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::updating(function ($model) {

            // 🔒 Ownership immutable
            if ($model->isDirty('company_id')) {
                throw new \LogicException('Company ownership cannot be changed.');
            }
        });

        static::saving(function ($model) {

            // 🔥 Prevent publish without approval
            if ($model->is_active && ! $model->isApproved()) {
                throw new \LogicException('Cannot activate job that is not approved.');
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function applicationLogs(): HasMany
    {
        return $this->hasMany(JobApplicationLog::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Core Publish Logic (Single Source of Truth)
    |--------------------------------------------------------------------------
    */

    private function isWithinPublishWindow(): bool
    {
        if (! $this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expired_at && $this->expired_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isPublished(): bool
    {
        return $this->approval_status === ApprovalStatus::APPROVED
            && $this->is_active
            && $this->isWithinPublishWindow();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes (SYNCED WITH HELPER)
    |--------------------------------------------------------------------------
    */

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('approval_status', ApprovalStatus::APPROVED->value)
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
    | State Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->approval_status === ApprovalStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->approval_status === ApprovalStatus::SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->approval_status === ApprovalStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === ApprovalStatus::REJECTED;
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    /*
    |--------------------------------------------------------------------------
    | Tracking Helpers (LEAN & VALID)
    |--------------------------------------------------------------------------
    */

    public function clickCount(): int
    {
        return $this->applicationLogs()
            ->where('event_type', 'click')
            ->count();
    }

    public function uniqueVisitorCount(): int
    {
        return $this->applicationLogs()
            ->where('event_type', 'click')
            ->whereNotNull('session_id')
            ->distinct('session_id')
            ->count('session_id');
    }

    public function applyCount(): int
    {
        return $this->applicationLogs()
            ->where('event_type', 'apply')
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_path
            ? Storage::url($this->poster_path)
            : null;
    }
}
