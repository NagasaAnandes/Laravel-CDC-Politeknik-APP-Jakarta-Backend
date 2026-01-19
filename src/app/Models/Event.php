<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(EventLog::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }
}
