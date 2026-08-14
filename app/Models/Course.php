<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
    ];
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class)->orderBy('position');
    }

    public function courseProgress()
    {
        return $this->hasMany(CourseProgress::class);
    }
}
