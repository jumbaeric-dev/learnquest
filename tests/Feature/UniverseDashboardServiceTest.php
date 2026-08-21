<?php

namespace Tests\Feature;

use App\Data\Child\Dashboard\UniverseDashboardDTO;
use App\Models\Child;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Module;
use App\Models\Badge;
use App\Models\Skill;
use App\Models\Subject;
use App\Services\Child\Dashboard\UniverseDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniverseDashboardServiceTest extends TestCase
{
  use RefreshDatabase;
  /**
   * A basic feature test example.
   */
  public function test_explorer_header_uses_configured_level_progression(): void
  {
    config([
      "learnquest.levels" => [
        1 => 0,
        2 => 250,
        3 => 600,
        4 => 1000,
      ],
    ]);

    $child = Child::factory()->create([
      "xp" => 400,
      "level" => 2,
    ]);

    $service = app(UniverseDashboardService::class);

    $header = $service->explorerHeader($child);

    $this->assertSame(2, $header->level);
    $this->assertSame(400, $header->xp);
    $this->assertSame(600, $header->nextLevelXp);
    $this->assertSame(43, $header->xpPercentage);
  }

  public function test_explorer_header_has_no_next_level_at_max_level(): void
  {
    config([
      "learnquest.levels" => [
        1 => 0,
        2 => 250,
        3 => 600,
      ],
    ]);

    $child = Child::factory()->create([
      "xp" => 700,
      "level" => 3,
    ]);

    $service = app(UniverseDashboardService::class);

    $header = $service->explorerHeader($child);

    $this->assertSame(3, $header->level);
    $this->assertSame(700, $header->xp);
    $this->assertNull($header->nextLevelXp);
    $this->assertSame(100, $header->xpPercentage);
  }

  public function test_skills_use_stored_skill_progress_percentage(): void
  {
    $child = Child::factory()->create();

    $skill = Skill::factory()->create();

    $child->skillProgress()->create([
      "skill_id" => $skill->id,
      "xp" => 250,
      "level" => 3,
      "progress_percentage" => 25,
    ]);

    $service = app(UniverseDashboardService::class);

    $skills = $service->skills($child);

    $this->assertCount(1, $skills);
    $this->assertSame(250, $skills[0]->xp);
    $this->assertSame(25, $skills[0]->progress);
  }

  protected function service(): UniverseDashboardService
  {
    return app(UniverseDashboardService::class);
  }

  public function test_dashboard_returns_complete_dashboard_dto(): void
  {
    $child = Child::factory()->create();

    $dashboard = $this->service()->dashboard($child);

    $this->assertInstanceOf(UniverseDashboardDTO::class, $dashboard);

    $this->assertNotNull($dashboard->explorer);
    $this->assertNotNull($dashboard->welcome);
    $this->assertNotNull($dashboard->nova);
    $this->assertIsArray($dashboard->skills);
    $this->assertIsArray($dashboard->achievements);
    $this->assertIsArray($dashboard->worlds);
    $this->assertIsArray($dashboard->recommendations);
    $this->assertNotNull($dashboard->dailyChallenge);
    $this->assertNotNull($dashboard->community);
  }

  public function test_dashboard_includes_current_mission_when_child_has_active_learning(): void
  {
    $child = Child::factory()->create();

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $module = $course->modules()->create([
      "title" => "Getting Started",
      "position" => 1,
    ]);

    $lesson = $module->lessons()->create([
      "title" => "First Lesson",
      "position" => 1,
      "estimated_minutes" => 10,
    ]);

    $activity = $lesson->activities()->create([
      "title" => "First Activity",
      "activity_type" => "quiz",
      "content" => [
        "question" => "What is learning?",
        "options" => [["option" => "A"], ["option" => "B"]],
        "answer" => 0,
      ],
      "position" => 1,
      "xp_reward" => 25,
      "is_published" => true,
    ]);

    $child->courseProgress()->create([
      "course_id" => $course->id,
      "progress_percentage" => 20,
      "completed" => false,
    ]);

    $dashboard = $this->service()->dashboard($child);

    $this->assertNotNull($dashboard->mission);

    $this->assertSame($course->title, $dashboard->mission->course);

    $this->assertSame($lesson->title, $dashboard->mission->lesson);

    $this->assertSame($activity->title, $dashboard->mission->activity);

    $this->assertSame(25, $dashboard->mission->xpReward);

    $this->assertSame(20, $dashboard->mission->progress);
  }

  public function test_dashboard_mission_is_null_when_child_has_nothing_to_continue(): void
  {
    $child = Child::factory()->create();

    $dashboard = $this->service()->dashboard($child);

    $this->assertNull($dashboard->mission);
    $this->assertNull($dashboard->learning);
  }

  public function test_achievements_are_limited_to_latest_five(): void
  {
    $child = Child::factory()->create();

    for ($i = 1; $i <= 6; $i++) {
      $badge = Badge::factory()->create([
        "name" => "Badge {$i}",
      ]);

      $child->badges()->attach($badge->id, [
        "earned_at" => now()->subMinutes(10 - $i),
      ]);
    }

    $achievements = $this->service()->achievements($child);

    $this->assertCount(5, $achievements);
  }

  public function test_skills_return_top_three_by_xp(): void
  {
    $child = Child::factory()->create();

    $skills = collect(range(1, 5))->map(
      fn($i) => Skill::factory()->create([
        "name" => "Skill {$i}",
      ])
    );

    foreach ($skills as $index => $skill) {
      $xp = ($index + 1) * 100;

      $child->skillProgress()->create([
        "skill_id" => $skill->id,
        "xp" => $xp,
        "level" => 1,
        "progress_percentage" => min(100, $xp / 10),
      ]);
    }

    $result = $this->service()->skills($child);

    $this->assertCount(3, $result);

    $this->assertSame("Skill 5", $result[0]->name);
    $this->assertSame("Skill 4", $result[1]->name);
    $this->assertSame("Skill 3", $result[2]->name);
  }

  public function test_learning_worlds_include_only_active_subjects_and_count_published_courses(): void
  {
    $activeSubject = Subject::factory()->create([
      "name" => "Active World",
      "is_active" => true,
    ]);

    $inactiveSubject = Subject::factory()->create([
      "name" => "Inactive World",
      "is_active" => false,
    ]);

    Course::factory()
      ->count(2)
      ->create([
        "subject_id" => $activeSubject->id,
        "is_published" => true,
      ]);

    Course::factory()->create([
      "subject_id" => $activeSubject->id,
      "is_published" => false,
    ]);

    Course::factory()->create([
      "subject_id" => $inactiveSubject->id,
      "is_published" => true,
    ]);

    $worlds = $this->service()->learningWorlds();

    $this->assertCount(1, $worlds);

    $this->assertSame("Active World", $worlds[0]->name);

    $this->assertSame(2, $worlds[0]->courses);
  }

  public function test_recommendations_include_published_courses_and_exclude_current_course(): void
  {
    $child = Child::factory()->create();

    $currentCourse = Course::factory()->create([
      "title" => "Current Course",
      "is_published" => true,
    ]);

    $child->courseProgress()->create([
      "course_id" => $currentCourse->id,
      "progress_percentage" => 25,
      "completed" => false,
    ]);

    Course::factory()->create([
      "title" => "Recommended Course",
      "is_published" => true,
    ]);

    Course::factory()->create([
      "title" => "Unpublished Course",
      "is_published" => false,
    ]);

    $recommendations = $this->service()->recommendedAdventures($child);

    $titles = array_map(
      fn($recommendation) => $recommendation->title,
      $recommendations
    );

    $this->assertContains("Recommended Course", $titles);

    $this->assertNotContains("Current Course", $titles);

    $this->assertNotContains("Unpublished Course", $titles);
  }
}
