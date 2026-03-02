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

        'is_active'   => 'boolean',
        'published_at' => 'datetime',
        'expired_at'  => 'date',

        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    */

    protected $attributes = [
        'approval_status' => 'draft',
        'is_active' => false,
    ];

    /*
    |--------------------------------------------------------------------------
    | Model Guard (Ownership Only)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::updating(function ($model) {

            // 🔒 Ownership immutable
            if ($model->isDirty('company_id')) {
                throw new \LogicException(
                    'Company ownership cannot be changed.'
                );
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
    | State Helpers (Pure Read-Only)
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->approval_status === ApprovalStatus::DRAFT;
    }

    public function isPending(): bool
    {
        return $this->approval_status === ApprovalStatus::PENDING;
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
        return $this->expired_at?->isPast() === true;
    }

    public function isPublishWindowOpen(): bool
    {
        if (! $this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        if ($this->isExpired()) {
            return false;
        }

        return true;
    }

    public function isPublished(): bool
    {
        return $this->isApproved()
            && $this->is_active
            && $this->isPublishWindowOpen();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
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
                    ->orWhereDate('expired_at', '>=', now());
            });
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
