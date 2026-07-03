<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;
    public const FOREIGN_KEY = 'learning_module_id';
    protected $fillable = [
        'learning_module_id',
        'title',
        'description',
        'position',
        'xp_reward',
        'estimated_minutes',
        'is_published',
    ];

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'learning_module_id');
    }

    public function activities()
    {
        return $this->hasMany(LessonActivity::class);
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
