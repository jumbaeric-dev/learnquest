<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class AIForKidsActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $activities = [

            [
                'lesson_title' => 'Meet AI',
                'title' => 'Meet Robo the Friendly Helper',
                'activity_type' => 'story',
                'xp_reward' => 10,
                'content' => [
                    'story_text' => 'Robo is a friendly AI helper who helps children learn new things every day.',
                ],
                'skills' => [
                    'AI Literacy',
                    'Communication',
                ],
            ],

            [
                'lesson_title' => 'Meet AI',
                'title' => 'What Is AI?',
                'activity_type' => 'quiz',
                'xp_reward' => 15,
                'content' => [
                    'question' => 'What does AI stand for?',
                    'options' => [
                        ['option' => 'Artificial Intelligence'],
                        ['option' => 'Animal Intelligence'],
                        ['option' => 'Automatic Internet'],
                    ],
                    'answer' => 0,
                ],
                'skills' => [
                    'AI Literacy',
                    'Critical Thinking',
                ],
            ],

            [
                'lesson_title' => 'AI Around Us',
                'title' => 'Find AI Around You',
                'activity_type' => 'project',
                'xp_reward' => 20,
                'content' => [
                    'project_description' =>
                    'Find 5 examples of AI that you use at home, school, or on a phone.',
                ],
                'skills' => [
                    'AI Literacy',
                    'Problem Solving',
                ],
            ],

            [
                'lesson_title' => 'How AI Learns',
                'title' => 'Teach the Robot',
                'activity_type' => 'ai_chat',
                'xp_reward' => 20,
                'content' => [
                    'system_prompt' =>
                    'You are a friendly robot learning from a child.',
                    'goal' =>
                    'Help learners understand that AI learns from examples.',
                ],
                'skills' => [
                    'AI Literacy',
                    'Communication',
                    'Critical Thinking',
                ],
            ],

            [
                'lesson_title' => 'How AI Learns',
                'title' => 'Train Your First AI',
                'activity_type' => 'coding_challenge',
                'xp_reward' => 25,
                'content' => [
                    'challenge' =>
                    'Show the robot pictures of cats and dogs so it can learn the difference.',
                    'difficulty' => 'easy',
                ],
                'skills' => [
                    'AI Literacy',
                    'Coding',
                    'Problem Solving',
                ],
            ],
        ];

        foreach ($activities as $activityData) {

            $lesson = Lesson::where(
                'title',
                $activityData['lesson_title']
            )->first();

            if (! $lesson) {
                continue;
            }

            $activity = LessonActivity::create([
                'lesson_id' => $lesson->id,
                'title' => $activityData['title'],
                'activity_type' => $activityData['activity_type'],
                'content' => $activityData['content'],
                'position' => 1,
                'xp_reward' => $activityData['xp_reward'],
                'is_published' => true,
            ]);

            $skillIds = Skill::whereIn(
                'name',
                $activityData['skills']
            )->pluck('id');

            $activity->skills()->sync($skillIds);
        }
    }
}
