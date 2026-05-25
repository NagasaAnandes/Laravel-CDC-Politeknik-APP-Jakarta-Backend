<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_vacancy_id',
        'user_id',
        'status',
        'note',
        'internal_note',
        'applied_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $attributes = [
        'status' => 'pending',
    ];

    protected $casts = [
        'status' => JobApplicationStatus::class,
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function jobVacancy(): BelongsTo
    {
        return $this->belongsTo(JobVacancy::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(JobApplicationStatusLog::class);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', JobApplicationStatus::PENDING->value);
    }

    public function scopeReviewed(Builder $query): Builder
    {
        return $query->where('status', JobApplicationStatus::REVIEWED->value);
    }

    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', JobApplicationStatus::ACCEPTED->value);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', JobApplicationStatus::REJECTED->value);
    }
}
