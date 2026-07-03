<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseProgress extends Model
{
    protected $fillable = [
        'child_id',
        'course_id',
        'completed_lessons',
        'total_lessons',
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

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}