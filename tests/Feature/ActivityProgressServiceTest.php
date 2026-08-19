<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\LessonActivity;
use App\Services\Progress\ActivityProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class ActivityProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_passing_score_marks_activity_as_completed(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $progress = $service->complete(
            $child,
            $activity,
            70
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(70, $progress->score);
        $this->assertSame(25, $progress->xp_earned);
        $this->assertNotNull($progress->completed_at);
    }

    public function test_score_below_passing_threshold_does_not_complete_activity(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $progress = $service->complete(
            $child,
            $activity,
            69
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(69, $progress->score);
        $this->assertSame(0, $progress->xp_earned);
        $this->assertNull($progress->completed_at);
    }

    public function test_activity_progress_can_be_updated_after_an_unsuccessful_attempt(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $firstProgress = $service->complete(
            $child,
            $activity,
            50
        );

        $secondProgress = $service->complete(
            $child,
            $activity,
            85
        );

        $this->assertFalse($firstProgress->completed);

        $this->assertTrue($secondProgress->completed);
        $this->assertSame(85, $secondProgress->score);
        $this->assertSame(25, $secondProgress->xp_earned);

        $this->assertDatabaseCount(
            'activity_progress',
            1
        );
    }

    public function test_activity_specific_xp_reward_is_used(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 40,
        ]);

        $service = app(ActivityProgressService::class);

        $progress = $service->complete(
            $child,
            $activity,
            90
        );

        $this->assertSame(40, $progress->xp_earned);
    }

    public function test_configured_default_xp_reward_is_available_for_unsaved_activity_without_reward(): void
    {
        config([
            'learnquest.activity_completion_xp' => 25,
        ]);

        $activity = new LessonActivity([
            'xp_reward' => null,
        ]);

        $service = app(ActivityProgressService::class);

        $this->assertSame(
            25,
            $service->xpRewardFor($activity)
        );
    }

    public function test_zero_score_is_valid_but_does_not_complete_activity(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $progress = $service->complete(
            $child,
            $activity,
            0
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->score);
        $this->assertSame(0, $progress->xp_earned);
    }

    public function test_score_above_100_is_rejected(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->complete(
            $child,
            $activity,
            101
        );
    }

    public function test_negative_score_is_rejected(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $this->expectException(InvalidArgumentException::class);

        $service->complete(
            $child,
            $activity,
            -1
        );
    }

    public function test_activity_progress_service_does_not_award_xp(): void
    {
        $child = Child::factory()->create([
            'xp' => 0,
        ]);

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $service->complete(
            $child,
            $activity,
            90
        );

        $child->refresh();

        $this->assertSame(
            0,
            $child->xp
        );
    }

    public function test_activity_progress_service_does_not_create_skill_progress(): void
    {
        $child = Child::factory()->create();

        $activity = LessonActivity::factory()->create([
            'xp_reward' => 25,
        ]);

        $service = app(ActivityProgressService::class);

        $service->complete(
            $child,
            $activity,
            90
        );

        $this->assertDatabaseCount(
            'child_skill_progress',
            0
        );
    }
}