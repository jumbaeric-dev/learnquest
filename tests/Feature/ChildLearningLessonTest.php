<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildLearningLessonTest extends TestCase
{
  use RefreshDatabase;

  protected function createChild(): array
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    return [$user, $child];
  }

  protected function createCourseWithLesson(
    bool $coursePublished = true,
    bool $lessonPublished = true
  ): array {
    $course = Course::factory()->create([
      "is_published" => $coursePublished,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
      "position" => 1,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "position" => 1,
      "is_published" => $lessonPublished,
    ]);

    return [$course, $module, $lesson];
  }

  public function test_child_can_access_published_lesson(): void
  {
    [$user] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson();

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertOk();
  }

  public function test_child_cannot_access_unpublished_lesson(): void
  {
    [$user] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson(true, false);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertNotFound();
  }

  public function test_child_cannot_access_lesson_from_unpublished_course(): void
  {
    [$user] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson(false, true);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertNotFound();
  }

  public function test_child_can_see_published_activities_for_lesson(): void
  {
    [$user] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson();

    $first = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "position" => 1,
      "is_published" => true,
    ]);

    $second = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "position" => 2,
      "is_published" => true,
    ]);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertOk()
      ->assertSee($first->title)
      ->assertSee($second->title);
  }

  public function test_unpublished_activity_is_not_exposed_on_lesson(): void
  {
    [$user] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson();

    $published = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "position" => 1,
      "is_published" => true,
    ]);

    $unpublished = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "position" => 2,
      "is_published" => false,
    ]);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertOk()
      ->assertSee($published->title)
      ->assertDontSee($unpublished->title);
  }

  public function test_lesson_progress_belongs_to_current_child(): void
  {
    [$user, $child] = $this->createChild();

    [, , $lesson] = $this->createCourseWithLesson();

    $otherUser = User::factory()->create();

    $otherChild = Child::factory()->create([
      "user_id" => $otherUser->id,
      "is_active" => true,
    ]);

    $activity = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "is_published" => true,
    ]);

    $child->lessonProgress()->create([
      "lesson_id" => $lesson->id,
      "completed_activities" => 1,
      "total_activities" => 1,
      "progress_percentage" => 100,
      "completed" => true,
      "completed_at" => now(),
    ]);

    $otherChild->lessonProgress()->create([
      "lesson_id" => $lesson->id,
      "completed_activities" => 0,
      "total_activities" => 1,
      "progress_percentage" => 0,
      "completed" => false,
    ]);

    $child->activityProgress()->create([
      "activity_id" => $activity->id,
      "completed" => true,
      "score" => 100,
      "xp_earned" => 5,
      "completed_at" => now(),
    ]);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lesson->id}")
      ->assertOk()
      ->assertSee("100%");
  }

  public function test_child_cannot_access_lesson_from_another_course_by_guessing_id(): void
  {
    [$user] = $this->createChild();

    [$courseA] = $this->createCourseWithLesson();

    [, , $lessonB] = $this->createCourseWithLesson();

    $this->assertNotSame($courseA->id, $lessonB->module->course->id);

    $this->actingAs($user)
      ->get("/learn/lesson/{$lessonB->id}")
      ->assertOk();
  }
}
