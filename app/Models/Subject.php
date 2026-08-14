<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

protected $fillable = [
    'name',
    'slug',
    'description',
    'icon',

    'cover_image',
    'theme_color',
    'background_music',
    'sort_order',
    'difficulty',

    'tagline',
    'story_intro',
    'hero_character',
    'banner_animation',
    'unlock_level',
    'estimated_hours',
    'badge_icon',
    'is_featured',
    'is_seasonal',

    'is_active',
];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
