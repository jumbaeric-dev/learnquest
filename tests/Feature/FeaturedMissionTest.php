<?php

namespace Tests\Feature;

use App\Livewire\Child\Missions\Components\FeaturedMission;
use App\Models\Child;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\User;
use App\Services\Child\Learning\LearningContentAccessService;
use App\Services\Progress\CourseProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class FeaturedMissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_mission_rejects_unpublished_course_before_updating_progress(): void
    {
        $user = User::factory()->create();

        $child = Child::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $course = Course::factory()->create([
            'is_published' => false,
        ]);

        $component = new FeaturedMission();
        $component->mission = ['id' => $course->id];

        $progress = $this->mock(CourseProgressService::class);
        $progress->shouldNotReceive('update');

        $this->actingAs($user);

        try {
            $component->startMission(
                app(\App\Services\Child\Context\CurrentChildService::class),
                $progress,
                app(LearningContentAccessService::class),
            );

            $this->fail('Expected an HTTP 404 for an unpublished course.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertDatabaseMissing('course_progress', [
            'child_id' => $child->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_start_mission_rejects_missing_child_context(): void
    {
        $user = User::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $component = new FeaturedMission();
        $component->mission = ['id' => $course->id];

        $progress = $this->mock(CourseProgressService::class);
        $progress->shouldNotReceive('update');

        $this->actingAs($user);

        try {
            $component->startMission(
                app(\App\Services\Child\Context\CurrentChildService::class),
                $progress,
                app(LearningContentAccessService::class),
            );

            $this->fail('Expected an HTTP 403 when no child context exists.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        $this->assertDatabaseCount('course_progress', 0);
    }

    public function test_start_mission_rejects_course_in_inactive_world_before_updating_progress(): void
    {
        $user = User::factory()->create();

        $child = Child::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $course->subject->update([
            'is_active' => false,
        ]);

        $component = new FeaturedMission();
        $component->mission = ['id' => $course->id];

        $progress = $this->mock(CourseProgressService::class);
        $progress->shouldNotReceive('update');

        $this->actingAs($user);

        try {
            $component->startMission(
                app(\App\Services\Child\Context\CurrentChildService::class),
                $progress,
                app(LearningContentAccessService::class),
            );

            $this->fail('Expected an HTTP 404 for a course in an inactive World.');
        } catch (HttpException $exception) {
            $this->assertSame(404, $exception->getStatusCode());
        }

        $this->assertDatabaseMissing('course_progress', [
            'child_id' => $child->id,
            'course_id' => $course->id,
        ]);
    }
}
