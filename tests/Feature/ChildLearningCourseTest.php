<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseProgress;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildLearningCourseTest extends TestCase
{
  use RefreshDatabase;

  public function test_child_can_see_course_lessons(): void
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
      ->assertSee($course->title)
      ->assertSee($module->title)
      ->assertSee($lesson->title);
  }

  public function test_course_page_does_not_link_to_future_unfinished_lessons(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      'user_id' => $user->id,
      'is_active' => true,
    ]);

    $course = Course::factory()->create([
      'is_published' => true,
    ]);

    $module = CourseModule::factory()->create([
      'course_id' => $course->id,
      'position' => 1,
    ]);

    $firstLesson = Lesson::factory()->create([
      'learning_module_id' => $module->id,
      'position' => 1,
      'is_published' => true,
    ]);

    $futureLesson = Lesson::factory()->create([
      'learning_module_id' => $module->id,
      'position' => 2,
      'is_published' => true,
    ]);

    CourseProgress::create([
      'child_id' => $child->id,
      'course_id' => $course->id,
      'completed_lessons' => 0,
      'total_lessons' => 2,
      'progress_percentage' => 0,
      'completed' => false,
      'completed_at' => null,
    ]);

    $response = $this->actingAs($user)
      ->get(route('learn.course', $course))
      ->assertOk()
      ->assertSee($firstLesson->title)
      ->assertSee($futureLesson->title)
      ->assertSee('Complete the previous lesson first');

    $response->assertSee(route('learn.lesson', $firstLesson), false);
    $response->assertDontSee(route('learn.lesson', $futureLesson), false);
  }

  public function test_non_current_course_shows_lessons_as_locked_until_started(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      'user_id' => $user->id,
      'is_active' => true,
    ]);

    $currentCourse = Course::factory()->create([
      'is_published' => true,
    ]);

    CourseProgress::create([
      'child_id' => $child->id,
      'course_id' => $currentCourse->id,
      'completed_lessons' => 0,
      'total_lessons' => 1,
      'progress_percentage' => 0,
      'completed' => false,
      'completed_at' => null,
    ]);

    $otherCourse = Course::factory()->create([
      'is_published' => true,
    ]);

    $module = CourseModule::factory()->create([
      'course_id' => $otherCourse->id,
    ]);

    $lesson = Lesson::factory()->create([
      'learning_module_id' => $module->id,
      'is_published' => true,
    ]);

    $response = $this->actingAs($user)
      ->get(route('learn.course', $otherCourse))
      ->assertOk()
      ->assertSee($lesson->title)
      ->assertSee('Start this course from the Adventure Path first');

    $response->assertDontSee(route('learn.lesson', $lesson), false);
  }

  public function test_course_progress_is_shown_for_current_child(): void
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

    $lessonOne = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $lessonTwo = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
      "position" => 2,
    ]);

    LessonProgress::create([
      "child_id" => $child->id,
      "lesson_id" => $lessonOne->id,
      "completed_activities" => 1,
      "total_activities" => 1,
      "progress_percentage" => 100,
      "completed" => true,
      "completed_at" => now(),
    ]);

    CourseProgress::create([
      "child_id" => $child->id,
      "course_id" => $course->id,
      "completed_lessons" => 1,
      "total_lessons" => 2,
      "progress_percentage" => 50,
      "completed" => false,
      "completed_at" => null,
    ]);

    $this->actingAs($user)
      ->get(route("learn.course", $course))
      ->assertOk()
      ->assertSee("50%")
      ->assertSee("1")
      ->assertSee("2");
  }

  public function test_course_progress_belongs_to_current_child(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "is_active" => true,
    ]);

    $otherUser = User::factory()->create();

    $otherChild = Child::factory()->create([
      "user_id" => $otherUser->id,
      "is_active" => true,
    ]);

    $course = Course::factory()->create([
      "is_published" => true,
    ]);

    CourseProgress::create([
      "child_id" => $otherChild->id,
      "course_id" => $course->id,
      "completed_lessons" => 10,
      "total_lessons" => 10,
      "progress_percentage" => 100,
      "completed" => true,
      "completed_at" => now(),
    ]);

    // The acting child needs their own (fresh) progress row for
    // the course to resolve as their current course — this is
    // what the test is actually verifying stays isolated.
    CourseProgress::create([
      "child_id" => $child->id,
      "course_id" => $course->id,
      "completed_lessons" => 0,
      "total_lessons" => 10,
      "progress_percentage" => 0,
      "completed" => false,
      "completed_at" => null,
    ]);

    $this->actingAs($user)
      ->get(route("learn.course", $course))
      ->assertOk()
      ->assertSee("0%")
      ->assertDontSee("100%");
  }

  public function test_unpublished_course_cannot_be_opened_by_child(): void
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

  public function test_unpublished_lesson_is_not_exposed_on_course(): void
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

    $publishedLesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => true,
    ]);

    $unpublishedLesson = Lesson::factory()->create([
      "learning_module_id" => $module->id,
      "is_published" => false,
    ]);

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
      ->assertSee($publishedLesson->title)
      ->assertDontSee($unpublishedLesson->title);
  }

}