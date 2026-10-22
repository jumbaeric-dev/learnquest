<?php

namespace Tests\Feature;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\DailyChallengeClaim;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\LessonProgress;
use App\Models\NovaMessage;
use App\Services\Child\Learning\DailyChallengeService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyChallengeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['learnquest.daily_challenges.timezone' => 'UTC']);

        Carbon::setTestNow('2026-10-23 10:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function challenge(Child $child, string $key): array
    {
        return collect(app(DailyChallengeService::class)->forChild($child))
            ->firstWhere('key', $key);
    }

    public function test_nothing_is_completed_or_claimed_at_the_start_of_the_day(): void
    {
        $child = Child::factory()->create();

        foreach (app(DailyChallengeService::class)->forChild($child) as $challenge) {
            $this->assertFalse($challenge['completed']);
            $this->assertFalse($challenge['claimed']);
        }
    }

    public function test_ask_nova_completes_only_from_a_user_message_sent_today(): void
    {
        $child = Child::factory()->create();

        // Assistant-only message does not count.
        NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'assistant',
            'content' => 'Hi!',
        ]);

        $this->assertFalse($this->challenge($child, 'ask_nova')['completed']);

        // A user message from yesterday does not count.
        $old = NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Yesterday',
        ]);
        $old->created_at = now()->subDay();
        $old->save();

        $this->assertFalse($this->challenge($child, 'ask_nova')['completed']);

        // A user message from today does.
        NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Today',
        ]);

        $this->assertTrue($this->challenge($child, 'ask_nova')['completed']);
    }

    public function test_complete_lesson_uses_real_lesson_completion_from_today(): void
    {
        $child = Child::factory()->create();
        $lesson = Lesson::factory()->create();

        $this->assertFalse($this->challenge($child, 'complete_lesson')['completed']);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lesson->id,
            'completed_activities' => 1,
            'total_activities' => 1,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $this->assertTrue($this->challenge($child, 'complete_lesson')['completed']);
    }

    public function test_create_something_requires_a_completed_project_activity(): void
    {
        $child = Child::factory()->create();

        $quiz = LessonActivity::factory()->create(['activity_type' => 'quiz']);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $quiz->id,
            'completed' => true,
            'score' => 100,
            'xp_earned' => 25,
            'completed_at' => now(),
        ]);

        $this->assertFalse($this->challenge($child, 'create_something')['completed']);

        $project = LessonActivity::factory()->create(['activity_type' => 'project']);

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $project->id,
            'completed' => true,
            'score' => 100,
            'xp_earned' => 25,
            'completed_at' => now(),
        ]);

        $this->assertTrue($this->challenge($child, 'create_something')['completed']);
    }

    public function test_claiming_a_completed_challenge_awards_xp_once(): void
    {
        $child = Child::factory()->create(['xp' => 0]);

        NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Hello Nova',
        ]);

        $service = app(DailyChallengeService::class);

        $result = $service->claim($child, 'ask_nova');

        $this->assertNotNull($result);
        $this->assertSame(10, $result['awarded']);
        $this->assertSame(10, $child->fresh()->xp);
        $this->assertTrue($this->challenge($child, 'ask_nova')['claimed']);

        // Second claim the same day does nothing.
        $this->assertNull($service->claim($child, 'ask_nova'));
        $this->assertSame(10, $child->fresh()->xp);
        $this->assertSame(1, DailyChallengeClaim::count());
    }

    public function test_cannot_claim_an_incomplete_or_unknown_challenge(): void
    {
        $child = Child::factory()->create(['xp' => 0]);

        $service = app(DailyChallengeService::class);

        $this->assertNull($service->claim($child, 'ask_nova'));
        $this->assertNull($service->claim($child, 'does_not_exist'));

        $this->assertSame(0, $child->fresh()->xp);
        $this->assertSame(0, DailyChallengeClaim::count());
    }

    public function test_a_challenge_can_be_claimed_again_the_next_day(): void
    {
        $child = Child::factory()->create(['xp' => 0]);

        $service = app(DailyChallengeService::class);

        NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Day one',
        ]);

        $this->assertNotNull($service->claim($child, 'ask_nova'));

        Carbon::setTestNow('2026-10-24 10:00:00');

        $this->assertFalse($this->challenge($child, 'ask_nova')['completed']);
        $this->assertNull($service->claim($child, 'ask_nova'));

        NovaMessage::create([
            'child_id' => $child->id,
            'role' => 'user',
            'content' => 'Day two',
        ]);

        $this->assertNotNull($service->claim($child, 'ask_nova'));
        $this->assertSame(20, $child->fresh()->xp);
    }
}