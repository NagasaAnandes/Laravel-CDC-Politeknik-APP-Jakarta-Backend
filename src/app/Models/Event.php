<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    use SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $guarded = [
        'approval_status',
        'is_active',
        'published_at',
        'submitted_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'approval_status' => ApprovalStatus::class,

        'is_active' => 'boolean',

        'registration_deadline' => 'date',

        'published_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'cancelled_at' => 'datetime',
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
    | Workflow Guard Flag
    |--------------------------------------------------------------------------
    */

    protected bool $bypassWorkflowGuard = false;

    public function bypassWorkflowGuard(): static
    {
        $this->bypassWorkflowGuard = true;
        return $this;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events (Hard Guard)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($model) {
            // ❌ HAPUS Auth::user() dependency (anti-pattern)
            // 👉 company_id harus di-set dari service/controller
        });

        static::updating(function ($model) {

            if ($model->bypassWorkflowGuard) {
                return;
            }

            // 🔒 Workflow protection
            if (
                $model->isDirty('approval_status') ||
                $model->isDirty('submitted_at') ||
                $model->isDirty('approved_at') ||
                $model->isDirty('approved_by') ||
                $model->isDirty('rejected_at') ||
                $model->isDirty('rejected_by') ||
                $model->isDirty('rejection_reason') ||
                $model->isDirty('cancelled_at') ||
                $model->isDirty('cancelled_by')
            ) {
                throw new \LogicException(
                    'Event workflow must be changed via ApprovalService.'
                );
            }

            // 🔒 Publication protection
            if (
                $model->isDirty('is_active') ||
                $model->isDirty('published_at')
            ) {
                throw new \LogicException(
                    'Publication must be changed via ApprovalService.'
                );
            }

            // 🔒 Ownership lock
            if (
                $model->isDirty('company_id') &&
                $model->getOriginal('company_id') !== null
            ) {
                throw new \LogicException(
                    'Event ownership cannot be changed.'
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

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function approvalLogs(): MorphMany
    {
        return $this->morphMany(ApprovalLog::class, 'approvable');
    }

    public function viewLogs()
    {
        return $this->hasMany(EventLog::class);
    }

    public function registrations()
    {
        // ✅ FIX: exclude soft deleted registrations
        return $this->hasMany(EventRegistration::class)
            ->whereNull('deleted_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle Helpers
    |--------------------------------------------------------------------------
    */

    public function isApproved(): bool
    {
        return $this->approval_status === ApprovalStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === ApprovalStatus::REJECTED;
    }

    public function isSubmitted(): bool
    {
        return $this->approval_status === ApprovalStatus::SUBMITTED;
    }

    public function isDraft(): bool
    {
        return $this->approval_status === ApprovalStatus::DRAFT;
    }

    public function isCancelled(): bool
    {
        return $this->cancelled_at !== null;
    }

    public function isRegistrationClosed(): bool
    {
        return $this->registration_deadline?->isPast() === true;
    }

    public function isPublishWindowOpen(): bool
    {
        if (! $this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        return true;
    }

    public function isPublished(): bool
    {
        return $this->isApproved()
            && ! $this->isCancelled()
            && $this->is_active
            && $this->isPublishWindowOpen();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePublished($query)
    {
        return $query
            ->where('approval_status', ApprovalStatus::APPROVED->value)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('cancelled_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Registration & Quota
    |--------------------------------------------------------------------------
    */

    public function isRegistrationOpen(): bool
    {
        return ! $this->isRegistrationClosed();
    }

    public function isQuotaFull(): bool
    {
        if ($this->quota === null) {
            return false;
        }

        return $this->registrations_count >= $this->quota;
    }

    public function canRegister(): bool
    {
        if ($this->registration_method !== 'internal') {
            return false;
        }

        if ($this->isRegistrationClosed()) {
            return false;
        }

        return $this->isPublished()
            && ! $this->isQuotaFull();
    }

    /*
    |--------------------------------------------------------------------------
    | Poster Accessor
    |--------------------------------------------------------------------------
    */

    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_path
            ? Storage::url($this->poster_path)
            : null;
    }
}
