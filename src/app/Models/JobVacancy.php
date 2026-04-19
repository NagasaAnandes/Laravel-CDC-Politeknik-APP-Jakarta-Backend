<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;
use App\Models\ApprovalLog;
use App\Models\User;

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
        'expired_at'   => 'datetime', // ✅ FIX (sebelumnya date)

        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    protected $attributes = [
        'approval_status' => 'draft',
        'is_active'       => false,
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
            if ($model->is_active && $model->approval_status !== ApprovalStatus::APPROVED) {
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

    public function applicationLogs()
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
                    ->orWhere('expired_at', '>=', now()); // ✅ FIX (no whereDate)
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
    | Tracking Helpers (Basic)
    |--------------------------------------------------------------------------
    */

    public function clickCount(): int
    {
        return $this->applicationLogs()->count();
    }

    public function uniqueUserClickCount(): int
    {
        return $this->applicationLogs()
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');
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
