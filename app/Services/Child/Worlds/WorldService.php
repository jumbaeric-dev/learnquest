<?php

namespace App\Services\Child\Worlds;

use App\Data\Child\World\ContinueJourneyDTO;
use App\Data\Child\World\WorldCourseDTO;
use App\Data\Child\World\WorldDTO;
use App\Data\Child\World\WorldHeroDTO;
use App\Data\Child\World\WorldProgressDTO;
use App\Models\Child;
use App\Models\Subject;
use App\Repositories\Child\WorldRepository;
use App\Services\Child\Learning\LearningJourneyService;

class WorldService
{
    public function __construct(
        protected WorldRepository $worlds,
        protected LearningJourneyService $journey,
    ) {}

    /**
     * Build the complete World page.
     */
    public function world(
        Child $child,
        Subject $subject,
    ): WorldDTO {
        $subject = $this->worlds->find($subject->id);

        return new WorldDTO(
            hero: $this->hero($subject),
            journey: $this->continueJourney($child, $subject),
            progress: $this->progress($child, $subject),
            courses: $this->courses($child, $subject),
        );
    }

    /**
     * Build World hero data.
     */
    protected function hero(
        Subject $subject,
    ): WorldHeroDTO {
        return new WorldHeroDTO(
            id: $subject->id,
            name: $subject->name,
            tagline: $subject->tagline,
            description: $subject->description,
            storyIntro: $subject->story_intro,
            heroCharacter: $subject->hero_character,
            icon: $subject->icon,
            coverImage: $subject->cover_image,
            themeColor: $subject->theme_color,
            difficulty: ucfirst($subject->difficulty),
            courses: $subject->courses_count
                ?? $subject->courses->count(),
        );
    }

    /**
     * Build Continue Journey data.
     */
    protected function continueJourney(
        Child $child,
        Subject $subject,
    ): ?ContinueJourneyDTO {
        $course = $this->journey->getCurrentCourse($child);

        if (! $course) {
            return null;
        }

        // Only show the journey when the active course
        // belongs to this World.
        if ($course->subject_id !== $subject->id) {
            return null;
        }

        $lesson = $this->journey->getCurrentLesson($child);

        return new ContinueJourneyDTO(
            course: $course->title,
            lesson: $lesson?->title ?? 'Start Learning',
            progress: $this->journey->getCourseProgressPercentage(
                $child,
                $course
            ),
            buttonText: 'Continue',
            buttonAction: 'continue',
        );
    }

    /**
     * Build overall World progress.
     */
    protected function progress(
        Child $child,
        Subject $subject,
    ): WorldProgressDTO {
        $totalCourses = $subject->courses->count();

        $completedCourses = $child
            ->courseProgress()
            ->whereIn(
                'course_id',
                $subject->courses->pluck('id')
            )
            ->where('completed', true)
            ->count();

        $percentage = $totalCourses > 0
            ? (int) round(
                ($completedCourses / $totalCourses) * 100
            )
            : 0;

        return new WorldProgressDTO(
            completedCourses: $completedCourses,
            totalCourses: $totalCourses,
            percentage: $percentage,
        );
    }

    /**
     * Build World course cards.
     *
     * @return WorldCourseDTO[]
     */
    protected function courses(
        Child $child,
        Subject $subject,
    ): array {
        return $subject
            ->courses
            ->map(function ($course) use ($child) {

                $progress = $child
                    ->courseProgress()
                    ->where('course_id', $course->id)
                    ->first();

                return new WorldCourseDTO(
                    id: $course->id,
                    title: $course->title,
                    description: $course->description,
                    icon: $course->thumbnail,
                    ageGroup: $course->age_group,
                    locked: false,
                    progress: $progress?->progress_percentage ?? 0,
                );
            })
            ->all();
    }
}