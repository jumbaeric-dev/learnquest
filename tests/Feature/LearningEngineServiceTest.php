<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Skill;
use App\Models\ActivityProgress;
use App\Services\BadgeService;
use Mockery;
use RuntimeException;
use App\Services\Progress\LearningEngineService;
use App\Services\Progress\ActivityProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LearningEngineServiceTest extends TestCase
{
  use RefreshDatabase;

  protected function createLearningActivity(
    ?Skill $skill = null,
    array $activityOverrides = []
  ): array {
    $course = Course::factory()->create();

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
    ]);

    $activity = LessonActivity::factory()->create(
      array_merge(
        [
          "lesson_id" => $lesson->id,
          "xp_reward" => 5,
        ],
        $activityOverrides
      )
    );

    if ($skill) {
      $activity->skills()->attach($skill->id, ["weight" => 1]);

      $activity->fresh();
    }

    return [
      "course" => $course,
      "module" => $module,
      "lesson" => $lesson,
      "activity" => $activity,
    ];
  }

  public function test_completing_activity_runs_full_learning_pipeline(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill);

    $engine = app(LearningEngineService::class);

    $result = $engine->completeActivity($child, $learning["activity"], 100);

    $this->assertNotNull($result["activity_progress"]);

    $this->assertNotNull($result["lesson_progress"]);

    $this->assertNotNull($result["course_progress"]);

    $this->assertNotNull($result["skill_progress"]);

    $this->assertNotNull($result["streak"]);

    $this->assertNotNull($result["level"]);

    $this->assertArrayHasKey("badges", $result);

    $this->assertArrayHasKey("achievements", $result);

    $this->assertArrayHasKey("future_readiness", $result);
  }

  public function test_activity_completion_awards_child_xp(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    $expectedXp = (int) $learning["activity"]->xp_reward;

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertSame($expectedXp, $child->fresh()->xp);
  }

  public function test_activity_completion_creates_skill_progress(): void
  {
    $child = Child::factory()->create();

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill);

    $result = app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertDatabaseHas("child_skill_progress", [
      "child_id" => $child->id,
      "skill_id" => $skill->id,
    ]);

    $this->assertNotEmpty($result["skill_progress"]);
  }

  public function test_activity_completion_creates_streak(): void
  {
    $child = Child::factory()->create();

    $learning = $this->createLearningActivity();

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertDatabaseHas("child_streaks", [
      "child_id" => $child->id,
      "current_streak" => 1,
    ]);
  }

  public function test_activity_completion_updates_level(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity(null, ["xp_reward" => 10]);

    config()->set("learnquest.levels", [
      1 => 0,
      2 => 10,
    ]);

    config()->set("learnquest.activity_completion_xp", 10);

    $result = app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertSame(2, $result["level"]["new_level"]);

    $this->assertSame(2, $child->fresh()->level);
  }

  public function test_eligible_badge_is_awarded(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $badge = Badge::factory()->create([
      "name" => "XP Beginner",
      "slug" => "xp-10",
      "xp_reward" => 5,
      "is_active" => true,
    ]);

    $learning = $this->createLearningActivity(null, ["xp_reward" => 10]);

    config()->set("learnquest.activity_completion_xp", 10);

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertDatabaseHas("badge_child", [
      "child_id" => $child->id,
      "badge_id" => $badge->id,
    ]);
  }

  public function test_badge_xp_is_awarded(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    Badge::factory()->create([
      "name" => "XP Beginner",
      "slug" => "xp-10",
      "xp_reward" => 5,
      "is_active" => true,
    ]);

    $learning = $this->createLearningActivity(null, ["xp_reward" => 10]);

    config()->set("learnquest.activity_completion_xp", 10);

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    /*
     * 10 XP from activity
     * + 5 XP from badge
     */
    $this->assertSame(15, $child->fresh()->xp);
  }

  public function test_badge_is_not_awarded_twice_when_learning_pipeline_is_repeated(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    Badge::factory()->create([
      "slug" => "xp-1",
      "xp_reward" => 10,
      "is_active" => true,
    ]);

    $engine = app(LearningEngineService::class);

    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterFirstCompletion = $child->fresh()->xp;

    $this->assertDatabaseCount("badge_child", 1);

    $engine->completeActivity($child, $learning["activity"], 100);

    $this->assertSame($xpAfterFirstCompletion, $child->fresh()->xp);

    $this->assertDatabaseCount("badge_child", 1);
  }

  public function test_inactive_eligible_badge_is_not_awarded(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    Badge::factory()->create([
      "slug" => "xp-1",
      "xp_reward" => 10,
      "is_active" => false,
    ]);

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertDatabaseCount("badge_child", 0);

    $this->assertSame(
      (int) $learning["activity"]->xp_reward,
      $child->fresh()->xp
    );
  }

  public function test_future_readiness_is_returned(): void
  {
    $child = Child::factory()->create();

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill);

    $result = app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertIsArray($result["future_readiness"]);

    $this->assertArrayHasKey("overall_score", $result["future_readiness"]);

    $this->assertArrayHasKey("level", $result["future_readiness"]);

    $this->assertArrayHasKey("skills", $result["future_readiness"]);

    $this->assertArrayHasKey("radar_data", $result["future_readiness"]);
  }

  public function test_failed_activity_does_not_complete_activity(): void
  {
    $child = Child::factory()->create();

    $learning = $this->createLearningActivity();

    $result = app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      0
    );

    $this->assertFalse($result["activity_progress"]->completed);

    $this->assertFalse($result["lesson_progress"]->completed);

    $this->assertFalse($result["course_progress"]->completed);
  }

  public function test_learning_engine_returns_expected_summary(): void
  {
    $child = Child::factory()->create();

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill);

    $result = app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    $this->assertEquals(
      [
        "activity_progress",
        "lesson_progress",
        "course_progress",
        "skill_progress",
        "streak",
        "xp",
        "level",
        "badges",
        "achievements",
        "future_readiness",
      ],
      array_keys($result)
    );
  }

  public function test_repeating_completed_activity_does_not_award_activity_xp_twice(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    config()->set("learnquest.activity_completion_xp", 10);

    $engine = app(LearningEngineService::class);

    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterFirstCompletion = $child->fresh()->xp;

    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterSecondCompletion = $child->fresh()->xp;

    $this->assertSame($xpAfterFirstCompletion, $xpAfterSecondCompletion);
  }

  public function test_repeating_completed_activity_does_not_award_skill_xp_twice(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill, [
      "xp_reward" => 10,
    ]);

    $engine = app(LearningEngineService::class);

    // First completion.
    $engine->completeActivity($child, $learning["activity"], 100);

    $skillProgressAfterFirstCompletion = $skill
      ->childProgress()
      ->where("child_id", $child->id)
      ->first();

    $this->assertNotNull($skillProgressAfterFirstCompletion);

    $xpAfterFirstCompletion = $skillProgressAfterFirstCompletion->xp;

    // Repeat the already-completed activity.
    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterSecondCompletion = $skill
      ->childProgress()
      ->where("child_id", $child->id)
      ->first()->xp;

    $this->assertSame($xpAfterFirstCompletion, $xpAfterSecondCompletion);
  }

  public function test_failed_activity_does_not_award_xp(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    app(LearningEngineService::class)->completeActivity(
      $child,
      $learning["activity"],
      69
    );

    $this->assertSame(0, $child->fresh()->xp);
  }

  public function test_failed_then_passed_activity_awards_xp_once(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    $activityXp = (int) $learning["activity"]->xp_reward;

    $this->assertSame(5, $activityXp);

    $engine = app(LearningEngineService::class);

    // Failed attempt.
    $engine->completeActivity($child, $learning["activity"], 50);

    $this->assertSame(0, $child->fresh()->xp);

    // First successful completion.
    $engine->completeActivity($child, $learning["activity"], 80);

    $xpAfterPassing = $child->fresh()->xp;

    $this->assertSame($activityXp, $xpAfterPassing);

    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterPassing = $child->fresh()->xp;

    $this->assertSame($xpAfterPassing, $child->fresh()->xp);
  }

  public function test_passed_then_failed_activity_does_not_remove_xp(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    $engine = app(LearningEngineService::class);

    // First completion.
    $engine->completeActivity($child, $learning["activity"], 100);

    $xpAfterPassing = $child->fresh()->xp;

    // Later failed submission must not revoke the reward.
    $engine->completeActivity($child, $learning["activity"], 40);

    $this->assertSame($xpAfterPassing, $child->fresh()->xp);

    $this->assertDatabaseHas("activity_progress", [
      "child_id" => $child->id,
      "activity_id" => $learning["activity"]->id,
      "completed" => true,
    ]);
  }

  public function test_activity_progress_records_the_same_xp_reward_awarded_to_progression(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill, [
      "xp_reward" => 5,
    ]);

    $engine = app(LearningEngineService::class);

    $engine->completeActivity($child, $learning["activity"], 100);

    $progress = ActivityProgress::query()
      ->where("child_id", $child->id)
      ->where("activity_id", $learning["activity"]->id)
      ->firstOrFail();

    $this->assertSame(5, $progress->xp_earned);

    $this->assertSame(5, $child->fresh()->xp);

    $skillProgress = $skill
      ->childProgress()
      ->where("child_id", $child->id)
      ->firstOrFail();

    $this->assertSame(5, $skillProgress->xp);
  }

  public function test_explicit_activity_reward_is_the_single_reward_amount_across_progression_layers(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill, [
      "xp_reward" => 37,
    ]);

    $engine = app(LearningEngineService::class);

    $engine->completeActivity($child, $learning["activity"], 100);

    $progress = ActivityProgress::query()
      ->where("child_id", $child->id)
      ->where("activity_id", $learning["activity"]->id)
      ->firstOrFail();

    $this->assertSame(37, $progress->xp_earned);

    $this->assertSame(37, $child->fresh()->xp);

    $skillProgress = $skill
      ->childProgress()
      ->where("child_id", $child->id)
      ->firstOrFail();

    $this->assertSame(37, $skillProgress->xp);
  }

  public function test_progression_is_atomic_when_downstream_badge_evaluation_fails(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $learning = $this->createLearningActivity();

    /*
     * Make the activity eligible for a badge.
     */
    Badge::factory()->create([
      "slug" => "xp-1",
      "xp_reward" => 5,
      "is_active" => true,
    ]);

    /*
     * Force a failure after activity/skill/streak/XP/level
     * processing has already started.
     */
    $badgeService = Mockery::mock(BadgeService::class);

    $badgeService
      ->shouldReceive("evaluate")
      ->once()
      ->andThrow(new RuntimeException("Simulated badge evaluation failure."));

    $this->app->instance(BadgeService::class, $badgeService);

    $engine = app(LearningEngineService::class);

    try {
      $engine->completeActivity($child, $learning["activity"], 100);

      $this->fail("Expected badge evaluation failure.");
    } catch (RuntimeException $e) {
      $this->assertSame(
        "Simulated badge evaluation failure.",
        $e->getMessage()
      );
    }

    /*
     * The entire progression event must have rolled back.
     */

    $this->assertSame(0, $child->fresh()->xp);

    $this->assertSame(1, $child->fresh()->level);

    $this->assertDatabaseMissing("activity_progress", [
      "child_id" => $child->id,
      "activity_id" => $learning["activity"]->id,
      "completed" => true,
    ]);

    $this->assertDatabaseMissing("child_skill_progress", [
      "child_id" => $child->id,
    ]);

    $this->assertDatabaseMissing("child_streaks", [
      "child_id" => $child->id,
    ]);

    $this->assertDatabaseMissing("badge_child", [
      "child_id" => $child->id,
    ]);
  }

  public function test_completed_activity_is_idempotent_across_repeated_completion_requests(): void
  {
    $child = Child::factory()->create([
      "xp" => 0,
      "level" => 1,
    ]);

    $skill = Skill::factory()->create();

    $learning = $this->createLearningActivity($skill, [
      "xp_reward" => 25,
    ]);

    $engine = app(LearningEngineService::class);

    /*
     * First completion creates the progression event.
     */
    $firstResult = $engine->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    /*
     * Capture every progression value that must remain
     * unchanged when the same completion is submitted again.
     */
    $xpAfterFirst = $child->fresh()->xp;

    $skillXpAfterFirst = $skill
      ->childProgress()
      ->where("child_id", $child->id)
      ->firstOrFail()->xp;

    $activityProgressAfterFirst = ActivityProgress::query()
      ->where("child_id", $child->id)
      ->where("activity_id", $learning["activity"]->id)
      ->firstOrFail();

    /*
     * Second completion request.
     */
    $secondResult = $engine->completeActivity(
      $child,
      $learning["activity"],
      100
    );

    /*
     * Child XP must not increase.
     */
    $this->assertSame($xpAfterFirst, $child->fresh()->xp);

    /*
     * Skill XP must not increase.
     */
    $this->assertSame(
      $skillXpAfterFirst,
      $skill
        ->childProgress()
        ->where("child_id", $child->id)
        ->firstOrFail()->xp
    );

    /*
     * There must still be exactly one activity-progress row.
     */
    $this->assertDatabaseCount("activity_progress", 1);

    /*
     * The activity must remain completed.
     */
    $this->assertDatabaseHas("activity_progress", [
      "child_id" => $child->id,
      "activity_id" => $learning["activity"]->id,
      "completed" => true,
      "xp_earned" => 25,
    ]);

    /*
     * The second request must still return the existing
     * completed activity state.
     */
    $this->assertTrue($secondResult["activity_progress"]->completed);
  }
}
