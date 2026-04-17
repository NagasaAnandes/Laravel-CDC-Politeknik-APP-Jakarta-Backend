<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Education extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'institution',
        'degree',
        'field_of_study',
        'start_year',
        'end_year',
        'is_current',
        'description',
    ];

    protected $casts = [
        'start_year' => 'integer',
        'end_year'   => 'integer',
        'is_current' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
