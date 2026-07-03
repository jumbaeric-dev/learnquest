<?php

namespace App\Models;

use App\Services\FutureReadinessService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Child extends Model
{
    use HasFactory;
    protected $appends = [
        'full_name',
        'future_readiness_score',
    ];
    protected $fillable = [
        'parent_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'avatar',
        'xp',
        'level',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active' => 'boolean',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(
            $this->first_name . ' ' . $this->last_name
        );
    }

    public function getFilamentNameAttribute(): string
    {
        return trim(
            "{$this->first_name} {$this->last_name}"
        );
    }
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function activityProgress()
    {
        return $this->hasMany(ActivityProgress::class);
    }

    public function addXp(int $xp): void
    {
        $this->increment('xp', $xp);

        $this->refresh();

        $newLevel = floor($this->xp / 100) + 1;

        if ($newLevel > $this->level) {
            $this->update([
                'level' => $newLevel,
            ]);
        }
    }

    public function getCompletedActivitiesCountAttribute()
    {
        return $this->activityProgress()
            ->where('completed', true)
            ->count();
    }

    public function getTotalXpAttribute()
    {
        return $this->xp;
    }

    public function awardBadge(Badge $badge): bool
    {
        if (
            $this->badges()
            ->where('badge_id', $badge->id)
            ->exists()
        ) {
            return false;
        }

        $this->badges()->attach(
            $badge->id,
            [
                'earned_at' => now(),
            ]
        );

        $this->addXp($badge->xp_reward);

        return true;
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class)
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    public function getBadgesCountAttribute()
    {
        return $this->badges()->count();
    }

    public function lessonProgress()
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function courseProgress()
    {
        return $this->hasMany(CourseProgress::class);
    }

    public function streak()
    {
        return $this->hasOne(ChildStreak::class);
    }

    public function skillProgress()
    {
        return $this->hasMany(
            ChildSkillProgress::class
        );
    }

    public function getFutureReadinessScoreAttribute(): float
    {
        return FutureReadinessService::calculateScore(
            $this
        );
    }

    public function strongestSkills(int $limit = 3)
    {
        return $this->skillProgress()
            ->with('skill')
            ->orderByDesc('xp')
            ->take($limit)
            ->get();
    }

    public function weakestSkills(int $limit = 3)
    {
        return $this->skillProgress()
            ->with('skill')
            ->orderBy('xp')
            ->take($limit)
            ->get();
    }

    public function getCoursesCompletedAttribute()
    {
        // future implementation
    }
}
