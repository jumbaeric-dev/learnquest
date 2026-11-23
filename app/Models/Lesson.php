<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $casts = ['translations' => 'array'];

    public const FOREIGN_KEY = 'learning_module_id';

    protected $fillable = [
        'translations',
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
        return $this->belongsTo(
            CourseModule::class,
            self::FOREIGN_KEY
        );
    }

    public function activities()
    {
        return $this->hasMany(
            LessonActivity::class,
            'lesson_id'
        )->orderBy('position');
    }

    public function progress()
    {
        return $this->hasMany(LessonProgress::class);
    }
}
