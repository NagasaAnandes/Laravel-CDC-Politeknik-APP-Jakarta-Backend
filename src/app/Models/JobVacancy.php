<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime',
        'expired_at' => 'date',
    ];
}
