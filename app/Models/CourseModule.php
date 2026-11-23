<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseModule extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $casts = ['translations' => 'array'];

    protected $table = 'learning_modules';
    public const FOREIGN_KEY = 'course_id';

    protected $fillable = [
        'translations',
        'course_id',
        'title',
        'description',
        'position',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, Lesson::FOREIGN_KEY)->orderBy('position');
    }
}