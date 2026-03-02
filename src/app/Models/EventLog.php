<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'action',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public const ACTION_VIEW = 'view';
    public const ACTION_REGISTER = 'register';
    public const ACTION_REDIRECT_REGISTER = 'redirect_register';

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }
}
