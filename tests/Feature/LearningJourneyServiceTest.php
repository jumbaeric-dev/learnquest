<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\CourseProgress;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Services\Child\Learning\LearningJourneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\ActivityProgress;
use App\Models\LessonProgress;

class LearningJourneyServiceTest extends TestCase
{
    use RefreshDatabase;

    protected LearningJourneyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(LearningJourneyService::class);
    }

    public function test_current_course_ignores_unpublished_course(): void
    {
        $child = Child::factory()->create();

        $unpublishedCourse = Course::factory()->create([
            'is_published' => false,
        ]);

        $publishedCourse = Course::factory()->create([
            'is_published' => true,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $unpublishedCourse->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $publishedCourse->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $currentCourse = $this->service->getCurrentCourse($child);

        $this->assertNotNull($currentCourse);
        $this->assertSame(
            $publishedCourse->id,
            $currentCourse->id
        );
    }

    public function test_current_course_ignores_course_in_inactive_world(): void
    {
        $child = Child::factory()->create();

        $inactiveWorld = \App\Models\Subject::factory()->create([
            'is_active' => false,
        ]);

        $course = Course::factory()->create([
            'subject_id' => $inactiveWorld->id,
            'is_published' => true,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $this->assertNull(
            $this->service->getCurrentCourse($child)
        );
    }

    public function test_current_lesson_ignores_unpublished_lessons(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $unpublishedLesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => false,
            'position' => 1,
        ]);

        $publishedLesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
            'position' => 2,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 2,
            'progress_percentage' => 0,
        ]);

        $currentLesson = $this->service->getCurrentLesson($child);

        $this->assertNotNull($currentLesson);
        $this->assertSame(
            $publishedLesson->id,
            $currentLesson->id
        );

        $this->assertNotSame(
            $unpublishedLesson->id,
            $currentLesson->id
        );
    }

    public function test_current_activity_ignores_unpublished_activities(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
        ]);

        $unpublishedActivity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'is_published' => false,
            'position' => 1,
        ]);

        $publishedActivity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'is_published' => true,
            'position' => 2,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $currentActivity = $this->service->getCurrentActivity($child);

        $this->assertNotNull($currentActivity);
        $this->assertSame(
            $publishedActivity->id,
            $currentActivity->id
        );

        $this->assertNotSame(
            $unpublishedActivity->id,
            $currentActivity->id
        );
    }

    public function test_current_mission_only_contains_published_learning_content(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
        ]);

        $activity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'is_published' => true,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $mission = $this->service->getCurrentMission($child);

        $this->assertNotNull($mission);

        $this->assertSame(
            $course->id,
            $mission['course']->id
        );

        $this->assertSame(
            $lesson->id,
            $mission['lesson']->id
        );

        $this->assertSame(
            $activity->id,
            $mission['activity']->id
        );
    }

    public function test_current_activity_advances_to_next_incomplete_activity(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
        ]);

        $firstActivity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'is_published' => true,
            'position' => 1,
        ]);

        $secondActivity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'is_published' => true,
            'position' => 2,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $this->assertSame(
            $firstActivity->id,
            $this->service->getCurrentActivity($child)?->id
        );

        ActivityProgress::create([
            'child_id' => $child->id,
            'activity_id' => $firstActivity->id,
            'completed' => true,
            'score' => 100,
            'xp_earned' => $firstActivity->xp_reward,
            'completed_at' => now(),
        ]);

        $this->assertSame(
            $secondActivity->id,
            $this->service->getCurrentActivity($child)?->id
        );
    }

    public function test_current_lesson_advances_to_next_incomplete_lesson(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $firstLesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
            'position' => 1,
        ]);

        $secondLesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
            'position' => 2,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 2,
            'progress_percentage' => 0,
        ]);

        LessonProgress::create([
            'child_id' => $child->id,
            'lesson_id' => $firstLesson->id,
            'completed_activities' => 1,
            'total_activities' => 1,
            'progress_percentage' => 100,
            'completed' => true,
            'completed_at' => now(),
        ]);

        $currentLesson = $this->service->getCurrentLesson($child);

        $this->assertNotNull($currentLesson);
        $this->assertSame(
            $secondLesson->id,
            $currentLesson->id
        );
    }

    public function test_current_course_advances_to_next_incomplete_course(): void
    {
        $child = Child::factory()->create();

        $firstCourse = Course::factory()->create([
            'is_published' => true,
        ]);

        $secondCourse = Course::factory()->create([
            'is_published' => true,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $firstCourse->id,
            'completed' => true,
            'completed_lessons' => 1,
            'total_lessons' => 1,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $secondCourse->id,
            'completed' => false,
            'completed_lessons' => 0,
            'total_lessons' => 1,
            'progress_percentage' => 0,
        ]);

        $currentCourse = $this->service->getCurrentCourse($child);

        $this->assertNotNull($currentCourse);
        $this->assertSame(
            $secondCourse->id,
            $currentCourse->id
        );
    }

    public function test_current_course_is_null_when_all_courses_are_completed(): void
    {
        $child = Child::factory()->create();

        $course = Course::factory()->create([
            'is_published' => true,
        ]);

        CourseProgress::create([
            'child_id' => $child->id,
            'course_id' => $course->id,
            'completed' => true,
            'completed_lessons' => 1,
            'total_lessons' => 1,
            'progress_percentage' => 100,
            'completed_at' => now(),
        ]);

        $this->assertNull(
            $this->service->getCurrentCourse($child)
        );

        $this->assertNull(
            $this->service->getCurrentLesson($child)
        );

        $this->assertNull(
            $this->service->getCurrentActivity($child)
        );
    }
}
