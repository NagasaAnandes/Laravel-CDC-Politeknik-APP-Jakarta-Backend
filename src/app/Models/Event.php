<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'event_type',
        'organizer',
        'location',
        'start_datetime',
        'end_datetime',
        'registration_method',
        'registration_url',
        'quota',
        'is_active',
        'published_at',
        'poster_path',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'published_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function viewLogs()
    {
        return $this->hasMany(EventLog::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Lifecycle
    |--------------------------------------------------------------------------
    */

    public function isPublished(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        // Event dianggap expired jika end_datetime sudah lewat
        if ($this->end_datetime && $this->end_datetime->isPast()) {
            return false;
        }

        return true;
    }

    public function scopePublished($query)
    {
        return $query
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where('end_datetime', '>=', now());
    }

    /*
    |--------------------------------------------------------------------------
    | Quota & Registration
    |--------------------------------------------------------------------------
    */

    public function isQuotaFull(): bool
    {
        // Unlimited quota
        if ($this->quota === null) {
            return false;
        }

        return $this->registrations()->count() >= $this->quota;
    }

    public function canRegister(): bool
    {
        // Redirect events tidak pakai internal registration
        if ($this->registration_method !== 'internal') {
            return false;
        }

        return $this->isPublished() && ! $this->isQuotaFull();
    }

    /*
    |--------------------------------------------------------------------------
    | Poster URL Accessor
    |--------------------------------------------------------------------------
    */
    public function getPosterUrlAttribute(): ?string
    {
        return $this->poster_path
            ? Storage::url($this->poster_path)
            : null;
    }
}
