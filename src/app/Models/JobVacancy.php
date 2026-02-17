<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'company_name',
        'location',
        'employment_type',
        'description',
        'external_apply_url',
        'is_active',
        'published_at',
        'expired_at',
        'poster_path',

    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'approval_status' => ApprovalStatus::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function applicationLogs()
    {
        return $this->hasMany(JobApplicationLog::class);
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

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        if ($this->approval_status !== ApprovalStatus::APPROVED) {
            return false;
        }

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

    public function isPubliclyVisible(): bool
    {
        return $this->isPublished();
    }

    public function scopePubliclyVisible($query)
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

    public function scopePublished($query)
    {
        return $query->publiclyVisible();
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
