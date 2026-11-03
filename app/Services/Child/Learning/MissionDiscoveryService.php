<?php

namespace App\Services\Child\Learning;

use App\Models\Child;
use App\Models\Course;
use App\Models\Subject;

class MissionDiscoveryService
{
    public function getCategories(): array
    {
        return Subject::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(fn(Subject $subject) => [
                'name' => $subject->name,
                'icon' => $subject->icon ?: '✨',
            ])
            ->all();
    }

    /**
     * Return published missions suitable for this child.
     */
    public function getMissions(
        Child $child,
        string $category = 'all',
    ): array {
        $ageGroup = $this->ageGroupFor($child);

        // Fail closed if the child's age is unknown or outside
        // the supported curriculum age range.
        if ($ageGroup === null) {
            return [];
        }

        $courses = Course::query()
            ->where('is_published', true)
            ->where('age_group', $ageGroup)
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
                fn($query) => $query->whereHas(
                    'subject',
                    fn($sub) => $sub->where('name', $category)
                )
            )
            ->get();

        return $courses
            ->map(fn(Course $course) => $this->toMission($course))
            ->all();
    }

    /**
     * Map a child's date of birth to a supported curriculum band.
     */
    private function ageGroupFor(Child $child): ?string
    {
        if (! $child->date_of_birth) {
            return null;
        }

        $age = $child->date_of_birth->age;

        return match (true) {
            $age >= 3 && $age <= 5 => '3-5',
            $age >= 6 && $age <= 9 => '6-9',
            $age >= 10 && $age <= 14 => '10-14',
            default => null,
        };
    }

    protected function toMission(Course $course): array
    {
        return [
            'id' => $course->id,
            'title' => $course->title,
            'category' => $course->subject?->name ?? 'Learning',
            'icon' => $course->subject?->icon ?: '✨',
            'description' => $course->description,
            'xp' => $this->totalXp($course),
            'time' => $this->totalMinutes($course) . ' mins',
            'difficulty' => $course->age_group,
        ];
    }

    protected function totalXp(Course $course): int
    {
        return (int) $course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->flatMap(fn($lesson) => $lesson->activities)
            ->sum('xp_reward');
    }

    protected function totalMinutes(Course $course): int
    {
        return (int) $course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->sum('estimated_minutes');
    }
}
