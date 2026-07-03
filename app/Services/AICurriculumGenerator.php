<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;

class AICurriculumGenerator
{
    public function generate(array $input): Course
    {
        $course = Course::create([
            'title' => $input['title'],
            'slug' => CurriculumSchemaHelper::uniqueCourseSlug($input['title']),
            'subject_id' => $input['subject_id'],
            'age_group' => CurriculumSchemaHelper::normalizeAgeGroup($input['age_group'] ?? null),
            'description' => $input['goal'] ?? null,
            'is_published' => false,
        ]);

        $modules = $this->generateModules($input['goal']);

        foreach ($modules as $moduleIndex => $moduleData) {
            $module = CourseModule::create([
                'course_id' => $course->id,
                'title' => $moduleData['title'],
                'description' => $moduleData['description'],
                'position' => $moduleIndex + 1,
            ]);

            foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                $lesson = Lesson::create([
                    'learning_module_id' => $module->id,
                    'title' => $lessonData['title'],
                    'description' => $lessonData['content'] ?? null,
                    'position' => $lessonIndex + 1,
                ]);

                foreach ($lessonData['activities'] as $activityIndex => $activityData) {
                    $activityType = CurriculumSchemaHelper::normalizeActivityType(
                        $activityData['type']
                    );

                    LessonActivity::create([
                        'lesson_id' => $lesson->id,
                        'activity_type' => $activityType,
                        'title' => $activityData['title'],
                        'content' => CurriculumSchemaHelper::normalizeActivityContent(
                            $activityData['content'] ?? null,
                            $activityType
                        ),
                        'position' => $activityIndex + 1,
                    ]);
                }
            }
        }

        return $course;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function generateModules(string $goal): array
    {
        return [
            [
                'title' => "Introduction to {$goal}",
                'description' => "Basic understanding of {$goal}",
                'lessons' => [
                    [
                        'title' => "What is {$goal}?",
                        'content' => 'Intro lesson',
                        'activities' => [
                            [
                                'type' => 'text',
                                'title' => 'Read introduction',
                                'content' => 'Simple explanation',
                            ],
                            [
                                'type' => 'quiz',
                                'title' => 'Quick check',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
