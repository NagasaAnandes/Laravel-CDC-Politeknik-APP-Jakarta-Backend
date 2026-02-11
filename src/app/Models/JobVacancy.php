<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class JobVacancy extends Model
{
    use HasFactory;

    protected $fillable = [
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
    ];

    /**
     * Determine whether the job is currently published.
     */
    public function isPublished(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expired_at && $this->expired_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Scope for published jobs.
     */
    public function scopePublished($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expired_at')
                    ->orWhereDate('expired_at', '>=', now());
            });
    }


    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_path
            ? Storage::url($this->poster_path)
            : null;
    }

    public function applicationLogs()
    {
        return $this->hasMany(JobApplicationLog::class);
    }
}
