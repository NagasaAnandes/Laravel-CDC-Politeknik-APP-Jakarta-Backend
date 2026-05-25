<?php

namespace App\Models;

use App\Enums\JobApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplicationStatusLog extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'job_application_id',
        'from_status',
        'to_status',
        'changed_by',
        'note',
    ];

    protected $casts = [
        'from_status' => JobApplicationStatus::class,
        'to_status' => JobApplicationStatus::class,
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (self $model) {
            $model->created_at = now();
        });
    }

    public function jobApplication(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
