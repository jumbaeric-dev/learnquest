<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lessonActivities()
    {
        return $this->belongsToMany(
            LessonActivity::class,
            'lesson_activity_skill',
            'skill_id',
            'activity_id'
        )
            ->withPivot('weight')
            ->withTimestamps();
    }

    public function childProgress()
    {
        return $this->hasMany(
            ChildSkillProgress::class
        );
    }
}
