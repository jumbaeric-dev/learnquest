<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PublishSelfDiscoveryCurriculumSeeder extends Seeder
{
    private const EXPECTED_AGE_GROUPS = [
        '10-14',
        '3-5',
        '6-9',
    ];

    public function run(): void
    {
        $courses = Course::query()
            ->whereHas(
                'subject',
                fn ($query) => $query->where('slug', 'self-discovery')
            )
            ->with([
                'subject',
                'modules.lessons.activities.skills',
            ])
            ->get();

        if ($courses->count() !== 3) {
            throw new RuntimeException(
                'Self-Discovery publication stopped: expected exactly three age-band courses.'
            );
        }

        $actualAgeGroups = $courses->pluck('age_group')
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($actualAgeGroups !== self::EXPECTED_AGE_GROUPS) {
            throw new RuntimeException(
                'Self-Discovery publication stopped: expected courses for ages 3-5, 6-9, and 10-14.'
            );
        }

        foreach ($courses as $course) {
            if (! $course->subject?->is_active) {
                throw new RuntimeException(
                    "Self-Discovery publication stopped: {$course->title} has an inactive world."
                );
            }

            if ($course->modules->count() !== 2) {
                throw new RuntimeException(
                    "Self-Discovery publication stopped: {$course->title} must have two modules."
                );
            }

            $lessons = $course->modules->flatMap(
                fn ($module) => $module->lessons
            );

            if ($lessons->count() !== 4) {
                throw new RuntimeException(
                    "Self-Discovery publication stopped: {$course->title} must have four lessons."
                );
            }

            foreach ($lessons as $lesson) {
                $activities = $lesson->activities;

                if ($activities->count() !== 2) {
                    throw new RuntimeException(
                        "Self-Discovery publication stopped: {$lesson->title} must have exactly two activities."
                    );
                }

                $stories = $activities->where('activity_type', 'story');
                $quizzes = $activities->where('activity_type', 'quiz');

                if ($stories->count() !== 1 || $quizzes->count() !== 1) {
                    throw new RuntimeException(
                        "Self-Discovery publication stopped: {$lesson->title} must have one story and one quiz."
                    );
                }

                foreach ($activities as $activity) {
                    if (! $activity->skills->contains(
                        fn ($skill) => (bool) $skill->is_active
                    )) {
                        throw new RuntimeException(
                            "Self-Discovery publication stopped: {$activity->title} has no active skill mapping."
                        );
                    }

                    $content = $activity->content;

                    if (! is_array($content)) {
                        throw new RuntimeException(
                            "Self-Discovery publication stopped: {$activity->title} has invalid content."
                        );
                    }

                    if ($activity->activity_type === 'story') {
                        if (
                            ! is_string($content['instructions'] ?? null)
                            || trim($content['instructions']) === ''
                        ) {
                            throw new RuntimeException(
                                "Self-Discovery publication stopped: {$activity->title} has no story instructions."
                            );
                        }

                        continue;
                    }

                    $options = $content['options'] ?? null;
                    $answer = $content['answer'] ?? null;

                    if (
                        ! is_string($content['question'] ?? null)
                        || trim($content['question']) === ''
                        || ! is_array($options)
                        || count($options) < 2
                        || ! is_int($answer)
                        || ! array_key_exists($answer, $options)
                    ) {
                        throw new RuntimeException(
                            "Self-Discovery publication stopped: {$activity->title} has an invalid quiz."
                        );
                    }

                    foreach ($options as $option) {
                        if (
                            ! is_array($option)
                            || ! is_string($option['option'] ?? null)
                            || trim($option['option']) === ''
                        ) {
                            throw new RuntimeException(
                                "Self-Discovery publication stopped: {$activity->title} has an invalid quiz option."
                            );
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($courses): void {
            foreach ($courses as $course) {
                $course->is_published = true;
                $course->save();

                foreach ($course->modules as $module) {
                    foreach ($module->lessons as $lesson) {
                        $lesson->is_published = true;
                        $lesson->save();

                        foreach ($lesson->activities as $activity) {
                            $activity->is_published = true;
                            $activity->save();
                        }
                    }
                }
            }
        });

        $this->command?->info(
            'Self-Discovery published: 3 age-band courses, 12 lessons, and 24 activities.'
        );
    }
}