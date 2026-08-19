<?php

namespace Tests\Feature;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\Lesson;
use App\Services\Progress\LessonProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LessonProgressService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LessonProgressService::class);
    }

    public function test_lesson_progress_is_created_when_no_activities_are_completed(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        $progress = $this->service->update(
            $child,
            $lesson
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->completed_activities);
        $this->assertSame(1, $progress->total_activities);
        $this->assertSame(0.0, (float) $progress->progress_percentage);
        $this->assertNull($progress->completed_at);
    }

    public function test_lesson_progress_reaches_fifty_percent_when_one_of_two_activities_is_completed(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $activityOne = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        $lesson->activities()->create([
            'title' => 'Activity Two',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question two?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 1,
            ],
            'position' => 2,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $activityOne->id,
            'completed' => true,
            'score' => 80,
            'xp_earned' => 10,
            'completed_at' => now(),
        ]);

        $progress = $this->service->update(
            $child,
            $lesson
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(1, $progress->completed_activities);
        $this->assertSame(2, $progress->total_activities);
        $this->assertSame(50.0, (float) $progress->progress_percentage);
        $this->assertNull($progress->completed_at);
    }

    public function test_lesson_is_completed_when_all_activities_are_completed(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $activityOne = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        $activityTwo = $lesson->activities()->create([
            'title' => 'Activity Two',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question two?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 1,
            ],
            'position' => 2,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        foreach ([$activityOne, $activityTwo] as $activity) {
            ActivityProgress::create([
                'child_id' => $child->id,
                'activity_id' => $activity->id,
                'completed' => true,
                'score' => 80,
                'xp_earned' => 10,
                'completed_at' => now(),
            ]);
        }

        $progress = $this->service->update(
            $child,
            $lesson
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(2, $progress->completed_activities);
        $this->assertSame(2, $progress->total_activities);
        $this->assertSame(100.0, (float) $progress->progress_percentage);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_failed_activity_does_not_count_as_completed(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $activity = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => false,
            'score' => 40,
            'xp_earned' => 0,
            'completed_at' => null,
        ]);

        $progress = $this->service->update(
            $child,
            $lesson
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->completed_activities);
        $this->assertSame(1, $progress->total_activities);
        $this->assertSame(0.0, (float) $progress->progress_percentage);
    }

    public function test_update_from_activity_uses_the_same_canonical_calculation(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $activity = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
            'score' => 90,
            'xp_earned' => 10,
            'completed_at' => now(),
        ]);

        $progress = $this->service->updateFromActivity(
            $child,
            $activity
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(1, $progress->completed_activities);
        $this->assertSame(1, $progress->total_activities);
        $this->assertSame(100.0, (float) $progress->progress_percentage);
    }

    public function test_recalculating_completed_lesson_does_not_award_xp(): void
    {
        $child = Child::factory()->create([
            'xp' => 0,
        ]);

        $lesson = Lesson::factory()->create();

        $activity = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
            'score' => 90,
            'xp_earned' => 10,
            'completed_at' => now(),
        ]);

        $this->service->update(
            $child,
            $lesson
        );

        $this->assertSame(0, $child->fresh()->xp);

        $this->service->update(
            $child,
            $lesson
        );

        $this->assertSame(0, $child->fresh()->xp);
    }

    public function test_is_completed_returns_false_when_no_progress_exists(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $this->assertFalse(
            $this->service->isCompleted(
                $child,
                $lesson
            )
        );
    }

    public function test_is_completed_returns_true_after_lesson_completion(): void
    {
        $child = Child::factory()->create();

        $lesson = Lesson::factory()->create();

        $activity = $lesson->activities()->create([
            'title' => 'Activity One',
            'activity_type' => 'quiz',
            'content' => [
                'question' => 'Question one?',
                'options' => [
                    ['option' => 'A'],
                    ['option' => 'B'],
                ],
                'answer' => 0,
            ],
            'position' => 1,
            'xp_reward' => 10,
            'is_published' => true,
        ]);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
            'score' => 90,
            'xp_earned' => 10,
            'completed_at' => now(),
        ]);

        $this->service->update(
            $child,
            $lesson
        );

        $this->assertTrue(
            $this->service->isCompleted(
                $child,
                $lesson
            )
        );
    }
}