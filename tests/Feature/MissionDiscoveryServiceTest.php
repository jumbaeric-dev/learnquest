<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\Subject;
use App\Services\Child\Learning\MissionDiscoveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MissionDiscoveryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MissionDiscoveryService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(MissionDiscoveryService::class);
    }

    public function test_missions_include_only_published_courses_in_active_worlds(): void
    {
        $activeWorld = Subject::factory()->create([
            'name' => 'Active World',
            'is_active' => true,
        ]);

        $inactiveWorld = Subject::factory()->create([
            'name' => 'Inactive World',
            'is_active' => false,
        ]);

        $publishedCourse = Course::factory()->create([
            'subject_id' => $activeWorld->id,
            'title' => 'Published Course',
            'is_published' => true,
        ]);

        $unpublishedCourse = Course::factory()->create([
            'subject_id' => $activeWorld->id,
            'title' => 'Unpublished Course',
            'is_published' => false,
        ]);

        $inactiveWorldCourse = Course::factory()->create([
            'subject_id' => $inactiveWorld->id,
            'title' => 'Inactive World Course',
            'is_published' => true,
        ]);

        $missions = $this->service->getMissions();

        $missionIds = collect($missions)
            ->pluck('id')
            ->all();

        $this->assertContains($publishedCourse->id, $missionIds);
        $this->assertNotContains($unpublishedCourse->id, $missionIds);
        $this->assertNotContains($inactiveWorldCourse->id, $missionIds);
    }

    public function test_mission_xp_and_time_include_only_published_learning_content(): void
    {
        $world = Subject::factory()->create([
            'is_active' => true,
        ]);

        $course = Course::factory()->create([
            'subject_id' => $world->id,
            'is_published' => true,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
            'estimated_minutes' => 10,
        ]);

        Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => false,
            'estimated_minutes' => 99,
        ]);

        $publishedLesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => true,
            'estimated_minutes' => 20,
        ]);

        LessonActivity::factory()->create([
            'lesson_id' => $publishedLesson->id,
            'is_published' => true,
            'xp_reward' => 25,
        ]);

        LessonActivity::factory()->create([
            'lesson_id' => $publishedLesson->id,
            'is_published' => false,
            'xp_reward' => 999,
        ]);

        $mission = collect($this->service->getMissions())
            ->firstWhere('id', $course->id);

        $this->assertNotNull($mission);

        $this->assertSame(25, $mission['xp']);
        $this->assertSame('30 mins', $mission['time']);
    }

    public function test_categories_include_only_active_worlds(): void
    {
        Subject::factory()->create([
            'name' => 'Active World',
            'is_active' => true,
        ]);

        Subject::factory()->create([
            'name' => 'Inactive World',
            'is_active' => false,
        ]);

        $categories = $this->service->getCategories();

        $categoryNames = collect($categories)
            ->pluck('name')
            ->all();

        $this->assertContains('Active World', $categoryNames);
        $this->assertNotContains('Inactive World', $categoryNames);
    }

    public function test_category_filter_returns_courses_from_that_active_world(): void
    {
        $targetWorld = Subject::factory()->create([
            'name' => 'Money Skills',
            'is_active' => true,
        ]);

        $otherWorld = Subject::factory()->create([
            'name' => 'Coding',
            'is_active' => true,
        ]);

        $targetCourse = Course::factory()->create([
            'subject_id' => $targetWorld->id,
            'is_published' => true,
        ]);

        $otherCourse = Course::factory()->create([
            'subject_id' => $otherWorld->id,
            'is_published' => true,
        ]);

        $missions = $this->service->getMissions('Money Skills');

        $missionIds = collect($missions)
            ->pluck('id')
            ->all();

        $this->assertContains($targetCourse->id, $missionIds);
        $this->assertNotContains($otherCourse->id, $missionIds);
    }
}
