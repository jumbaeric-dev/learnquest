<?php

namespace App\Services\Child\Learning;

use App\Models\Course;
use App\Models\Subject;

/**
 * "Missions" are LearnQuest's playful framing of the real course
 * catalog — a mission card always corresponds to a real, published
 * Course in an active World.
 */
class MissionDiscoveryService
{
    public function getCategories(): array
    {
        return Subject::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Subject $subject) => [
                'name' => $subject->name,
                'icon' => $subject->icon ?? '�',
            ])
            ->all();
    }

    public function getMissions(string $category = 'all'): array
    {
        $courses = Course::query()
            ->where('is_published', true)
            ->whereHas('subject', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'subject',
                'modules.lessons' => function ($query) {
                    $query
                        ->where('is_published', true)
                        ->orderBy('position');
                },
                'modules.lessons.activities' => function ($query) {
                    $query
                        ->where('is_published', true)
                        ->orderBy('position');
                },
            ])
            ->when(
                $category !== 'all',
                fn ($query) => $query->whereHas(
                    'subject',
                    fn ($sub) => $sub->where('name', $category)
                )
            )
            ->get();

        return $courses
            ->map(fn (Course $course) => $this->toMission($course))
            ->all();
    }

    protected function toMission(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'category' => $course->subject?->name ?? 'Learning',
            'icon' => $course->subject?->icon ?? '�',
            'description' => $course->description,
            'xp' => $this->totalXp($course),
            'time' => $this->totalMinutes($course).' mins',
            'difficulty' => $course->age_group,
        ];
    }

    protected function totalXp(Course $course): int
    {
        return (int) $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->flatMap(fn ($lesson) => $lesson->activities)
            ->sum('xp_reward');
    }

    protected function totalMinutes(Course $course): int
    {
        return (int) $course->modules
            ->flatMap(fn ($module) => $module->lessons)
            ->sum('estimated_minutes');
    }
}