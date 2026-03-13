<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerResponse extends Model
{
    protected $fillable = [
        'survey_id',
        'user_id',
        'submitted_at'
    ];

    protected $dates = [
        'submitted_at'
    ];

    public function survey()
    {
        return $this->belongsTo(TracerSurvey::class);
    }

    public function answers()
    {
        return $this->hasMany(TracerAnswer::class, 'response_id');
    }
}
