<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAICurriculumGenerator
{
    public function generate(array $input): Course
    {
        $response = OpenAI::chat()->create([
            'model' => 'gpt-4.1-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a curriculum design expert for kids education. Always respond in strict JSON only.',
                ],
                [
                    'role' => 'user',
                    'content' => $this->buildPrompt($input),
                ],
            ],
            'temperature' => 0.7,
        ]);

        $json = json_decode($response->choices[0]->message->content, true);

        if (! $json) {
            throw new \Exception('AI returned invalid JSON');
        }

        return $this->buildCourse($json, $input);
    }

    private function buildPrompt(array $input): string
    {
        $ageGroup = CurriculumSchemaHelper::normalizeAgeGroup($input['age_group'] ?? null);

        return "
Create a learning curriculum for kids.

Subject ID: {$input['subject_id']}
Title: {$input['title']}
Age Group: {$ageGroup}
Goal: {$input['goal']}

IMPORTANT:
- Align all lessons strictly to the subject
- Ensure activities match subject skills
- Do not mix unrelated topics
- Use activity types: video, story, quiz, audio, drag_drop, drawing, flashcard, ai_chat, coding_challenge, project

Return ONLY JSON in this format:

{
  \"course\": {
    \"title\": \"\",
    \"description\": \"\",
    \"subject_id\": {$input['subject_id']}
  },
  \"modules\": [
    {
      \"title\": \"\",
      \"description\": \"\",
      \"lessons\": [
        {
          \"title\": \"\",
          \"content\": \"\",
          \"activities\": [
            {
              \"type\": \"story|quiz|video\",
              \"title\": \"\",
              \"content\": \"\"
            }
          ]
        }
      ]
    }
  ]
}
";
    }

    private function buildCourse(array $json, array $input): Course
    {
        $title = $json['course']['title'] ?? $input['title'];

        $course = Course::create([
            'title' => $title,
            'slug' => CurriculumSchemaHelper::uniqueCourseSlug($title),
            'description' => $json['course']['description'] ?? $input['goal'],
            'subject_id' => $input['subject_id'],
            'age_group' => CurriculumSchemaHelper::normalizeAgeGroup($input['age_group'] ?? null),
            'is_published' => false,
        ]);

        foreach ($json['modules'] as $moduleIndex => $moduleData) {
            $module = CourseModule::create([
                'course_id' => $course->id,
                'title' => $moduleData['title'],
                'description' => $moduleData['description'] ?? null,
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
}
