<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerQuestion extends Model
{
    protected $fillable = [
        'survey_id',
        'section',
        'question_text',
        'type',
        'options',
        'scale_min',
        'scale_max',
        'is_required',
        'order'
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean'
    ];

    public function survey()
    {
        return $this->belongsTo(TracerSurvey::class);
    }
}
