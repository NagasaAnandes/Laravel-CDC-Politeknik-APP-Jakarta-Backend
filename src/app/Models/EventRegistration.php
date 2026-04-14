<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'deleted_at'    => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeForEvent($query, $event)
    {
        return $query->where(
            'event_id',
            $event instanceof Event ? $event->id : $event
        );
    }

    public function scopeForUser($query, $user)
    {
        return $query->where(
            'user_id',
            $user instanceof User ? $user->id : $user
        );
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS (OPTIONAL)
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }
}
