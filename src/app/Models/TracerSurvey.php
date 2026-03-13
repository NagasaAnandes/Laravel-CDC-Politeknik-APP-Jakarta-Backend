<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerSurvey extends Model
{
    protected $fillable = [
        'title',
        'year',
        'description',
        'is_active',
        'start_date',
        'end_date',
    ];

    public function questions()
    {
        return $this->hasMany(TracerQuestion::class, 'survey_id')
            ->orderBy('order');
    }

    public function responses()
    {
        return $this->hasMany(TracerResponse::class, 'survey_id');
    }
}
