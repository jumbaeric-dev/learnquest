<?php

namespace App\Livewire\Child\Explore\Components;

use App\Models\ActivityProgress;
use App\Models\Subject;
use App\Services\Child\Context\CurrentChildService;
use Livewire\Component;

class LearningWorlds extends Component
{
    public array $worlds = [];

    public function mount(CurrentChildService $currentChild): void
    {
        $child = $currentChild->current();

        if (! $child) {
            return;
        }

        $this->worlds = Subject::query()
            ->where('is_active', true)
            ->withCount('courses')
            ->orderBy('sort_order')
            ->get()
            ->map(fn(Subject $subject) => [
                'title' => $subject->name,
                'description' => $subject->tagline ?? $subject->description,
                'icon' => $subject->icon ?? '🌎',
                'theme' => $subject->theme_color,
                'progress' => $this->progressFor($child, $subject),
                'xp' => $this->xpEarnedIn($child, $subject),
                'locked' => false,
                'badge' => null,
                'slug' => $subject->slug,
            ])
            ->all();
    }

    /**
     * Average course-completion percentage for this child across
     * every course in this world/subject.
     */
    protected function progressFor($child, Subject $subject): float
    {
        $progresses = $child->courseProgress()
            ->whereHas(
                'course',
                fn($query) => $query->where('subject_id', $subject->id)
            )
            ->pluck('progress_percentage');

        if ($progresses->isEmpty()) {
            return 0;
        }

        return round($progresses->avg(), 1);
    }

    /**
     * Total XP this child has earned from activities anywhere
     * within this world/subject.
     */
    protected function xpEarnedIn($child, Subject $subject): int
    {
        return (int) ActivityProgress::query()
            ->where('child_id', $child->id)
            ->where('completed', true)
            ->whereHas(
                'activity.lesson.module.course',
                fn($query) => $query->where('subject_id', $subject->id)
            )
            ->sum('xp_earned');
    }

    public function render()
    {
        return view('livewire.child.explore.components.learning-worlds');
    }
}
