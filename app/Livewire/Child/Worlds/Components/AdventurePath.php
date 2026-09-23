<?php

namespace App\Livewire\Child\Worlds\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Learning\LearningJourneyService;
use App\Services\Progress\CourseProgressService;
use Livewire\Attributes\Locked;
use Livewire\Component;

class AdventurePath extends Component
{
    /**
     * Course cards for this world, as built by WorldService
     * (WorldCourseDTO[]: id, title, description, icon, ageGroup,
     * locked, progress).
     */
    #[Locked]
    public array $courses = [];

    public array $nodes = [];

    public function mount(
        array $courses,
        CurrentChildService $currentChild,
        LearningJourneyService $journey,
    ): void {
        $this->courses = $courses;

        $child = $currentChild->current();

        $currentCourseId = $journey->getCurrentCourse($child)?->id;

        $this->nodes = collect($courses)
            ->map(function (array $course) use ($currentCourseId) {
                $status = match (true) {
                    $course['progress'] >= 100 => 'completed',
                    $course['id'] === $currentCourseId => 'current',
                    default => 'available',
                };

                return [
                    'id' => $course['id'],
                    'title' => $course['title'],
                    'description' => $course['description'],
                    'icon' => $course['icon'] ?? '📘',
                    'status' => $status,
                    'xp' => $this->totalXp($course['id']),
                ];
            })
            ->all();
    }

    /**
     * A child taps any unlocked node to jump straight into that
     * course. Starting a course they haven't touched before
     * creates the initial CourseProgress row LearningContentAccessService
     * requires before it will let them view it.
     */
    public function startCourse(
        int $courseId,
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
    ) {
        $child = $currentChild->current();

        $course = Course::findOrFail($courseId);

        $courseProgress->update($child, $course);

        return $this->redirectRoute('learn.course', $course);
    }

    protected function totalXp(int $courseId): int
    {
        $course = Course::with('modules.lessons.activities')->find($courseId);

        if (! $course) {
            return 0;
        }

        return (int) $course->modules
            ->flatMap(fn($module) => $module->lessons)
            ->flatMap(fn($lesson) => $lesson->activities)
            ->sum('xp_reward');
    }

    public function render()
    {
        return view(
            'livewire.child.worlds.components.adventure-path'
        );
    }
}
