<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'title',
        'slug',
        'description',
        'age_group',
        'thumbnail',
        'is_published',
        'position',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function modules()
    {
        return $this->hasMany(
            CourseModule::class,
            CourseModule::FOREIGN_KEY
        )->orderBy('position');
    }

    public function courseProgress()
    {
        return $this->hasMany(CourseProgress::class);
    }
}
