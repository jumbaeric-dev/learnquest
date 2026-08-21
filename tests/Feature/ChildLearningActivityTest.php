<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Child\Learning\Activity;
use Tests\TestCase;

class ChildLearningActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function createChild(): array
    {
        $user = User::factory()->create();

        $child = Child::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        return [$user, $child];
    }

    protected function createActivity(
        bool $coursePublished = true,
        bool $lessonPublished = true,
        bool $activityPublished = true,
    ): array {
        $course = Course::factory()->create([
            'is_published' => $coursePublished,
        ]);

        $module = CourseModule::factory()->create([
            'course_id' => $course->id,
        ]);

        $lesson = Lesson::factory()->create([
            'learning_module_id' => $module->id,
            'is_published' => $lessonPublished,
        ]);

        $activity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'activity_type' => 'quiz',
            'is_published' => $activityPublished,
            'content' => [
                'question' => 'What is 2 + 2?',
                'options' => [
                    ['option' => '3'],
                    ['option' => '4'],
                    ['option' => '5'],
                ],
                'answer' => 1,
            ],
            'xp_reward' => 10,
        ]);

        return [
            $course,
            $module,
            $lesson,
            $activity,
        ];
    }

    public function test_child_can_access_published_activity(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertOk()
            ->assertSee($activity->title);
    }

    public function test_child_cannot_access_unpublished_activity(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity(
            true,
            true,
            false
        );

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertNotFound();
    }

    public function test_child_cannot_access_activity_from_unpublished_lesson(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity(
            true,
            false,
            true
        );

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertNotFound();
    }

    public function test_child_cannot_access_activity_from_unpublished_course(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity(
            false,
            true,
            true
        );

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertNotFound();
    }

    public function test_child_sees_activity_question_and_options(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertOk()
            ->assertSee('What is 2 + 2?')
            ->assertSee('3')
            ->assertSee('4')
            ->assertSee('5');
    }

    public function test_correct_quiz_answer_completes_activity(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity,
            ])
            ->call('submitOption', 1)
            ->assertSet('submitted', true)
            ->assertSet('score', 100)
            ->assertSet('progress.completed', true);

        $this->assertDatabaseHas('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
            'score' => 100,
        ]);
    }

    public function test_wrong_quiz_answer_does_not_complete_activity(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity,
            ])
            ->call('submitOption', 0)
            ->assertSet('submitted', true)
            ->assertSet('score', 0)
            ->assertSet('progress.completed', false);

        $this->assertDatabaseHas('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => false,
            'score' => 0,
        ]);
    }

    public function test_correct_answer_is_not_exposed_in_rendered_html(): void
    {
        [$user] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        $response = $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertOk();

        $response->assertSee('What is 2 + 2?');

        /*
         * The option itself is visible, but the content's
         * answer index must not appear as exposed quiz data.
         *
         * This is a basic regression guard. The actual
         * correctness decision happens server-side.
         */
        $response->assertDontSee('"answer":1');
    }

    public function test_activity_completion_uses_current_child(): void
    {
        [$user, $child] = $this->createChild();

        $otherUser = User::factory()->create();

        $otherChild = Child::factory()->create([
            'user_id' => $otherUser->id,
            'is_active' => true,
        ]);

        [, , , $activity] = $this->createActivity();

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity,
            ])
            ->call('submitOption', 1);

        $this->assertDatabaseHas('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
        ]);

        $this->assertDatabaseMissing('activity_progress', [
            'child_id' => $otherChild->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function test_completed_activity_cannot_be_completed_again(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity,
            ])
            ->call('submitOption', 1)
            ->call('submitOption', 1);

        $this->assertDatabaseCount('activity_progress', 1);

        $this->assertDatabaseHas('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
            'completed' => true,
        ]);
    }

    public function test_guest_cannot_access_activity(): void
    {
        [, , , $activity] = $this->createActivity();

        $this->get("/learn/activity/{$activity->id}")
            ->assertForbidden();
    }

    public function test_authenticated_user_without_child_cannot_access_activity(): void
    {
        $user = User::factory()->create();

        [, , , $activity] = $this->createActivity();

        $this->actingAs($user)
            ->get("/learn/activity/{$activity->id}")
            ->assertForbidden();
    }
}