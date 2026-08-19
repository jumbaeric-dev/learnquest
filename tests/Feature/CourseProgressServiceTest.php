<?php

namespace Tests\Feature;

use App\Models\ActivityProgress;
use App\Models\Child;
use App\Models\Course;
use App\Models\LessonProgress;
use App\Services\Progress\CourseProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseProgressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CourseProgressService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CourseProgressService::class);
    }

    public function test_course_progress_is_created_when_no_lessons_are_completed(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->completed_lessons);
        $this->assertSame(1, $progress->total_lessons);
        $this->assertSame(
            0.0,
            (float) $progress->progress_percentage
        );
        $this->assertNull($progress->completed_at);
    }

    public function test_course_progress_reaches_fifty_percent_when_one_of_two_lessons_is_completed(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lessonOne = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        $module->lessons()->create([
            'title' => 'Lesson Two',
            'position' => 2,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lessonOne->id,
            'completed_activities' => 2,
            'total_activities' => 2,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(1, $progress->completed_lessons);
        $this->assertSame(2, $progress->total_lessons);
        $this->assertSame(
            50.0,
            (float) $progress->progress_percentage
        );
        $this->assertNull($progress->completed_at);
    }

    public function test_course_is_completed_when_all_lessons_are_completed(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lessonOne = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        $lessonTwo = $module->lessons()->create([
            'title' => 'Lesson Two',
            'position' => 2,
        ]);

        foreach ([$lessonOne, $lessonTwo] as $lesson) {
            LessonProgress::create([
                'child_id' => $child->id,
                'lesson_id' => $lesson->id,
                'completed_activities' => 2,
                'total_activities' => 2,
                'progress_percentage' => 100,
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(2, $progress->completed_lessons);
        $this->assertSame(2, $progress->total_lessons);
        $this->assertSame(
            100.0,
            (float) $progress->progress_percentage
        );
        $this->assertNotNull($progress->completed_at);
    }

    public function test_incomplete_lessons_do_not_count_toward_course_completion(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lessonOne = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        $module->lessons()->create([
            'title' => 'Lesson Two',
            'position' => 2,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lessonOne->id,
            'completed_activities' => 1,
            'total_activities' => 2,
            'progress_percentage' => 50,
            'completed' => false,
            'completed_at' => null,
        ]);

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->completed_lessons);
        $this->assertSame(2, $progress->total_lessons);
        $this->assertSame(
            0.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_lessons_from_another_course_do_not_affect_progress(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $otherCourse = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Target Module',
            'position' => 1,
        ]);

        $otherModule = $otherCourse->modules()->create([
            'title' => 'Other Module',
            'position' => 1,
        ]);

        $targetLesson = $module->lessons()->create([
            'title' => 'Target Lesson',
            'position' => 1,
        ]);

        $otherLesson = $otherModule->lessons()->create([
            'title' => 'Other Lesson',
            'position' => 1,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $targetLesson->id,
            'completed_activities' => 2,
            'total_activities' => 2,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $otherLesson->id,
            'completed_activities' => 2,
            'total_activities' => 2,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(1, $progress->completed_lessons);
        $this->assertSame(1, $progress->total_lessons);
    }

    public function test_empty_course_is_not_marked_completed(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $progress = $this->service->update(
            $child,
            $course
        );

        $this->assertFalse($progress->completed);
        $this->assertSame(0, $progress->completed_lessons);
        $this->assertSame(0, $progress->total_lessons);
        $this->assertSame(
            0.0,
            (float) $progress->progress_percentage
        );
        $this->assertNull($progress->completed_at);
    }

    public function test_update_from_lesson_uses_the_same_canonical_calculation(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lesson->id,
            'completed_activities' => 1,
            'total_activities' => 1,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $progress = $this->service->updateFromLesson(
            $child,
            $lesson
        );

        $this->assertTrue($progress->completed);
        $this->assertSame(1, $progress->completed_lessons);
        $this->assertSame(1, $progress->total_lessons);
        $this->assertSame(
            100.0,
            (float) $progress->progress_percentage
        );
    }

    public function test_recalculating_course_progress_does_not_award_xp(): void
    {
        $child = Child::factory()->create([
            'xp' => 0,
        ]);

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lesson->id,
            'completed_activities' => 1,
            'total_activities' => 1,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $this->service->update(
            $child,
            $course
        );

        $this->assertSame(0, $child->fresh()->xp);

        $this->service->update(
            $child,
            $course
        );

        $this->assertSame(0, $child->fresh()->xp);
    }

    public function test_is_completed_returns_false_when_no_progress_exists(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $this->assertFalse(
            $this->service->isCompleted(
                $child,
                $course
            )
        );
    }

    public function test_is_completed_returns_true_after_course_completion(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create();

        $module = $course->modules()->create([
            'title' => 'Module One',
            'position' => 1,
        ]);

        $lesson = $module->lessons()->create([
            'title' => 'Lesson One',
            'position' => 1,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $lesson->id,
            'completed_activities' => 1,
            'total_activities' => 1,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $this->service->update(
            $child,
            $course
        );

        $this->assertTrue(
            $this->service->isCompleted(
                $child,
                $course
            )
        );
    }
}