<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityProgress extends Model
{
    protected $table = 'activity_progress';

    protected $fillable = [
        'child_id',
        'activity_id',
        'completed',
        'score',
        'xp_earned',
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

    public function activity()
    {
        return $this->belongsTo(LessonActivity::class, 'activity_id');
    }
}