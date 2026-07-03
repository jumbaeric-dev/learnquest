<?php

namespace App\Services;

use App\Models\Child;
use Illuminate\Support\Collection;

class FutureReadinessService
{
    public static function build(Child $child): array
    {
        $skills = $child
            ->skillProgress()
            ->with('skill')
            ->get();

        return [
            'overall_score' => self::calculateScore($child),

            'level' => self::calculateLevel($child),

            'skills' => $skills->map(function ($progress) {

                return [
                    'skill' => $progress->skill->name,
                    'xp' => $progress->xp,
                    'level' => $progress->level,
                    'progress' => $progress->progress_percentage,
                ];
            }),

            'strongest_skills' => self::strongestSkills($child),

            'weakest_skills' => self::weakestSkills($child),

            'radar_data' => self::radarData($child),
        ];
    }

    public static function calculateScore(Child $child): float
    {
        $skills = $child->skillProgress;

        if ($skills->isEmpty()) {
            return 0;
        }

        return round(
            $skills->avg('progress_percentage'),
            2
        );
    }

    /**
     * Readiness level title.
     */
    public static function calculateLevel(Child $child): string
    {
        $score = self::calculateScore($child);

        return match (true) {
            $score >= 90 => 'Future Ready',
            $score >= 75 => 'Visionary',
            $score >= 60 => 'Creator',
            $score >= 45 => 'Builder',
            $score >= 25 => 'Innovator',
            default => 'Explorer',
        };
    }

    /**
     * Strongest skills.
     */
    public static function strongestSkills(
        int $limit = 5
    ) {
        return \App\Models\Skill::with('childProgress')
            ->get()
            ->map(function ($skill) {

                $avgProgress = round(
                    $skill->childProgress
                        ->avg('progress_percentage') ?? 0,
                    2
                );

                $skill->average_progress = $avgProgress;

                return $skill;
            })
            ->sortByDesc('average_progress')
            ->take($limit);
    }

    public static function strongestSkill(
        Child $child
    ): ?string {
        return $child->skillProgress()
            ->with('skill')
            ->orderByDesc('xp')
            ->first()
            ?->skill
            ?->name;
    }

    /**
     * Weakest skills.
     */
    public static function weakestSkills(
        int $limit = 5
    ) {
        return \App\Models\Skill::with('childProgress')
            ->get()
            ->map(function ($skill) {

                $avgProgress = round(
                    $skill->childProgress
                        ->avg('progress_percentage') ?? 0,
                    2
                );

                $skill->average_progress = $avgProgress;

                return $skill;
            })
            ->sortBy('average_progress')
            ->take($limit);
    }

    public static function weakestSkill(
        Child $child
    ): ?string {
        return $child->skillProgress()
            ->with('skill')
            ->orderBy('xp')
            ->first()
            ?->skill
            ?->name;
    }

    /**
     * Radar chart dataset.
     */
    public static function radarData(
        Child $child
    ): array {
        $skills = $child->skillProgress()
            ->with('skill')
            ->get();

        return [
            'labels' => $skills
                ->pluck('skill.name')
                ->toArray(),

            'values' => $skills
                ->pluck('progress_percentage')
                ->map(fn($value) => (float) $value)
                ->toArray(),
        ];
    }

    /**
     * Full learner profile.
     */
    public static function profile(
        Child $child
    ): array {
        return [
            'score' => self::calculateScore($child),
            'level' => self::calculateLevel($child),
            'strongest_skills' => $child->strongestSkills(),
            'weakest_skills' => $child->weakestSkills(),
            'radar_data' => self::radarData($child),
        ];
    }

    /**
     * Platform-wide average readiness.
     */
    public static function platformAverage(): float
    {
        $children = Child::with('skillProgress')->get();

        if ($children->isEmpty()) {
            return 0;
        }

        return round(
            $children->avg(
                fn($child) => self::calculateScore($child)
            ),
            2
        );
    }

    /**
     * Top future-ready learners.
     */
    public static function topLearners(
        int $limit = 10
    ): Collection {
        return Child::with('skillProgress')
            ->get()
            ->sortByDesc(
                fn($child) => self::calculateScore($child)
            )
            ->take($limit)
            ->values();
    }
}
