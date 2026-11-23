<?php

namespace App\Models;

use App\Models\Concerns\HasLocalizedActivityContent;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LessonActivity extends Model
{
  use HasFactory;
  use HasTranslations;
  use HasLocalizedActivityContent;

  protected $table = "activities"; // existing table

  protected $fillable = [
    "translations",
    "lesson_id",
    "title",
    "activity_type",
    "content",
    "position",
    "xp_reward",
    "is_published",
  ];

  protected $casts = [
    "content" => "array",
    "translations" => "array",
    "is_published" => "boolean",
  ];

  public function lesson()
  {
    return $this->belongsTo(Lesson::class);
  }

  public function skills()
  {
    return $this->belongsToMany(
      Skill::class,
      "lesson_activity_skill",
      "activity_id",
      "skill_id"
    )
      ->withPivot("weight")
      ->withTimestamps();
  }

  public function progress()
  {
    return $this->hasMany(ActivityProgress::class);
  }
}
