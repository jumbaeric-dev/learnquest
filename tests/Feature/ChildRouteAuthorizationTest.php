<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildRouteAuthorizationTest extends TestCase
{
  use RefreshDatabase;

  public function test_authenticated_user_without_child_cannot_access_child_routes(): void
  {
    $user = User::factory()->create();

    $this->actingAs($user);

    $routes = [
      "/child",
      "/child/my-universe",
      "/child/explore",
      "/child/missions",
    ];

    foreach ($routes as $route) {
      $this->get($route)->assertForbidden();
    }
  }

  public function test_authenticated_user_without_child_cannot_access_learning_routes(): void
  {
    $user = User::factory()->create();

    $course = Course::factory()->create();
    $lesson = Lesson::factory()->create();
    $activity = LessonActivity::factory()->create();

    $this->actingAs($user);

    $this->get("/learn/course/{$course->id}")->assertForbidden();

    $this->get("/learn/lesson/{$lesson->id}")->assertForbidden();

    $this->get("/learn/activity/{$activity->id}")->assertForbidden();
  }
}
