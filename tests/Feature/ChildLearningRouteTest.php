<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseProgress;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildLearningRouteTest extends TestCase
{
  use RefreshDatabase;

  public function test_authenticated_child_can_access_published_course(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    // The course must be the child's active learning journey
    // course for LearningContentAccessService to allow access.
    CourseProgress::create([
      "child_id" => $child->id,
      "course_id" => $course->id,
      "completed_lessons" => 0,
      "total_lessons" => 1,
      "progress_percentage" => 0,
      "completed" => false,
      "completed_at" => null,
    ]);

    $this->actingAs($user)
      ->get(route("learn.course", $course))
      ->assertOk()
      ->assertSee($course->title);
  }

  public function test_authenticated_child_cannot_access_unpublished_course(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => false,
    ]);

    $this->actingAs($user)
      ->get(route("learn.course", $course))
      ->assertNotFound();
  }

  public function test_authenticated_child_can_access_published_lesson(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    // The lesson's course must be the child's current course, and
    // the lesson itself must be the next incomplete lesson in it,
    // for LearningContentAccessService to allow access.
    CourseProgress::create([
      "child_id" => $child->id,
      "course_id" => $course->id,
      "completed_lessons" => 0,
      "total_lessons" => 1,
      "progress_percentage" => 0,
      "completed" => false,
      "completed_at" => null,
    ]);

    $this->actingAs($user)
      ->get(route("learn.lesson", $lesson))
      ->assertOk()
      ->assertSee($lesson->title);
  }

  public function test_authenticated_child_cannot_access_unpublished_lesson(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => false,
    ]);

    $this->actingAs($user)
      ->get(route("learn.lesson", $lesson))
      ->assertNotFound();
  }

  public function test_authenticated_child_cannot_access_lesson_from_unpublished_course(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => false,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $this->actingAs($user)
      ->get(route("learn.lesson", $lesson))
      ->assertNotFound();
  }

  public function test_authenticated_child_can_access_published_activity(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $activity = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "is_published" => true,
    ]);

    // The activity's lesson and course must resolve as the
    // child's current lesson/course for access to be allowed.
    CourseProgress::create([
      "child_id" => $child->id,
      "course_id" => $course->id,
      "completed_lessons" => 0,
      "total_lessons" => 1,
      "progress_percentage" => 0,
      "completed" => false,
      "completed_at" => null,
    ]);

    $this->actingAs($user)
      ->get(route("learn.activity", $activity))
      ->assertOk()
      ->assertSee($activity->title);
  }

  public function test_authenticated_child_cannot_access_unpublished_activity(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $activity = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "is_published" => false,
    ]);

    $this->actingAs($user)
      ->get(route("learn.activity", $activity))
      ->assertNotFound();
  }

  public function test_authenticated_child_cannot_access_activity_from_unpublished_course(): void
  {
    $user = User::factory()->create();

    Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => false,
    ]);

    $module = CourseModule::factory()->create([
      "course_id" => $course->id,
    ]);

    $lesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $activity = LessonActivity::factory()->create([
      "lesson_id" => $lesson->id,
      "is_published" => true,
    ]);

    $this->actingAs($user)
      ->get(route("learn.activity", $activity))
      ->assertNotFound();
  }

  public function test_authenticated_user_without_child_cannot_access_learning_content(): void
  {
    $user = User::factory()->create();

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    $this->actingAs($user)
      ->get(route("learn.course", $course))
      ->assertForbidden();
  }
}