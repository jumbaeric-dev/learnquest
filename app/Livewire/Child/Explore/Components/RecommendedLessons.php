<?php

namespace App\Livewire\Child\Explore\Components;

use App\Models\Course;
use App\Services\Child\Context\CurrentChildService;
use App\Services\Child\Dashboard\UniverseDashboardService;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Child\Learning\LearningJourneyService;
use App\Services\Progress\CourseProgressService;
use Livewire\Component;

class RecommendedLessons extends Component
{
    public array $lessons = [];

    public function mount(
        CurrentChildService $currentChild,
        UniverseDashboardService $dashboardService,
    ): void {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $this->lessons = collect($dashboardService->recommendedAdventures($child))
            ->take(3)
            ->map(function ($adventure) {
                $course = Course::with('modules.lessons')->find($adventure->id);

                $lesson = $course?->modules
                    ->flatMap(fn($module) => $module->lessons)
                    ->firstWhere('is_published', true);

                if (! $lesson) {
                    return null;
                }

                return [
                    'courseId' => $course->id,
                    'courseTitle' => $course->title,
                    'lessonTitle' => $lesson->title,
                    'minutes' => $lesson->estimated_minutes,
                    'icon' => $adventure->icon ?? '📘',
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    public function startLesson(
        int $courseId,
        CurrentChildService $currentChild,
        CourseProgressService $courseProgress,
        LearningJourneyService $journey,
        LearningContentAccessService $access,
    ) {
        $child = $currentChild->current();

        $course = Course::findOrFail($courseId);

        $course = $access->course($course);

        $courseProgress->update($child, $course);

        $lesson = $journey->getCurrentLesson($child);

        if (! $lesson) {
            return $this->redirectRoute('learn.course', $course);
        }

        return $this->redirectRoute('learn.lesson', $lesson);
    }

    public function render()
    {
        return view('livewire.child.explore.components.recommended-lessons');
    }
}
