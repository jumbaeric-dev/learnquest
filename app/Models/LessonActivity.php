<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonActivity extends Model
{
    use HasFactory;

    protected $table = 'activities'; // existing table

    protected $fillable = [
        'lesson_id',
        'title',
        'activity_type',
        'content',
        'position',
        'xp_reward',
        'is_published',
    ];

    protected $casts = [
        'content' => 'array',
        'is_published' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

public function skills()
    {
        return $this->belongsToMany(
            Skill::class,
            'lesson_activity_skill',
            'activity_id',
            'skill_id'
        )
            ->withPivot('weight')
            ->withTimestamps();
    }

    public function progress()
    {
        return $this->hasMany(ActivityProgress::class);
    }
}
