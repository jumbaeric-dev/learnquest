<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $fillable = [
        'child_id',
        'lesson_id',
        'completed_activities',
        'total_activities',
        'progress_percentage',
        'completed',
        'completed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function child()
    {
        return $this->belongsTo(Child::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}