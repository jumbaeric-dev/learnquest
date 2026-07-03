<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\LessonProgress;
use App\Models\CourseProgress;
use App\Services\Progress\ActivityProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ActivityProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_activities_updates_progress_and_awards_first_lesson_badge(): void
    {
        Permission::create(['name' => 'manage courses']);

        Badge::factory()->create([
            'slug' => 'first-lesson',
            'name' => 'First Lesson',
        ]);

        $course = Course::factory()->create();
        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
            'position' => 1,
        ]);
        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'position' => 1,
            'xp_reward' => 20,
        ]);

        $activities = LessonActivity::factory()
            ->count(2)
            ->sequence(
                ['position' => 1, 'xp_reward' => 5],
                ['position' => 2, 'xp_reward' => 5],
            )
            ->create([
                'lesson_id' => $lesson->id,
            ]);

        $child = Child::factory()->create([
            'xp' => 0,
            'level' => 1,
        ]);

        $service = app(ActivityProgressService::class);

        foreach ($activities as $activity) {
            $service->complete($child, $activity, 85);
        }

        $child->refresh();

        $this->assertSame(30, $child->xp);
        $this->assertTrue(
            LessonProgress::query()
                ->where('child_id', $child->id)
                ->where('lesson_id', $lesson->id)
                ->value('completed')
        );
        $this->assertTrue(
            CourseProgress::query()
                ->where('child_id', $child->id)
                ->where('course_id', $course->id)
                ->value('completed')
        );
        $this->assertTrue(
            $child->badges()->where('slug', 'first-lesson')->exists()
        );
    }

    public function test_course_module_lessons_use_learning_module_foreign_key(): void
    {
        $course = Course::factory()->create();
        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);
        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
        ]);

        $this->assertTrue(
            $course->modules()->first()->lessons->contains($lesson)
        );
    }
}
