<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'action',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public const ACTION_VIEW = 'view';
    public const ACTION_REGISTER = 'register';
    public const ACTION_REDIRECT_REGISTER = 'redirect_register';

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    public static function allowedActions(): array
    {
        return [
            self::ACTION_VIEW,
            self::ACTION_REGISTER,
            self::ACTION_REDIRECT_REGISTER,
        ];
    }

    protected static function booted()
    {
        static::creating(function ($model) {

            if (! in_array($model->action, self::allowedActions(), true)) {
                throw new \InvalidArgumentException('Invalid event log action.');
            }

            // enforce timestamp
            $model->created_at = now();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
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
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isGuest(): bool
    {
        return $this->user_id === null;
    }

    public function isView(): bool
    {
        return $this->action === self::ACTION_VIEW;
    }

    public function isRegister(): bool
    {
        return $this->action === self::ACTION_REGISTER;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    public function scopeView($query)
    {
        return $query->where('action', self::ACTION_VIEW);
    }

    public function scopeRegister($query)
    {
        return $query->where('action', self::ACTION_REGISTER);
    }
}
