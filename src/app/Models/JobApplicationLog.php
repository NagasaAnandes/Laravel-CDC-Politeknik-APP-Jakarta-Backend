<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobApplicationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'job_vacancy_id',
        'user_id',
        'clicked_at',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function jobVacancy()
    {
        return $this->belongsTo(JobVacancy::class);
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
