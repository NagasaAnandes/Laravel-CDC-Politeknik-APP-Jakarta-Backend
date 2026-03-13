<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerAnswer extends Model
{
    protected $fillable = [
        'response_id',
        'question_id',
        'answer_value',
        'answer_json'
    ];

    protected $casts = [
        'answer_json' => 'array'
    ];

    public function response()
    {
        return $this->belongsTo(TracerResponse::class);
    }

    public function question()
    {
        return $this->belongsTo(TracerQuestion::class);
    }
}
