<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalLog extends Model
{
    protected $table = 'approval_logs';

    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'from_status',
        'to_status',
        'action',
        'performed_by',
        'reason',
    ];

    public function approvable(): MorphTo
    {
        return $this->morphTo();
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
