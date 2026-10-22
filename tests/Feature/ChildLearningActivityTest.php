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
use Livewire\Livewire;
use App\Livewire\Child\Learning\Activity;
use Tests\TestCase;

class ChildLearningActivityTest extends TestCase
{
    use RefreshDatabase;

    protected ?Child $currentChild = null;

    protected function createChild(): array
    {
        $user = User::factory()->create();

        $child = Child::factory()->create([
            'user_id' => $user->id,
            'is_active' => true,
        ]);

        $this->currentChild = $child;

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

        if ($this->currentChild !== null) {
            CourseProgress::create([
                'child_id' => $this->currentChild->id,
                'course_id' => $course->id,
                'completed_lessons' => 0,
                'total_lessons' => 1,
                'progress_percentage' => 0,
                'completed' => false,
                'completed_at' => null,
            ]);
        }

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

    public function test_quiz_cannot_be_completed_through_generic_complete_action(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();
        $xpBefore = (int) $child->fresh()->xp;

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity,
            ])
            ->call('complete')
            ->assertStatus(403);

        $this->assertDatabaseMissing('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
        ]);

        $this->assertSame($xpBefore, (int) $child->fresh()->xp);
    }

    public function test_quiz_with_missing_answer_cannot_mark_first_option_correct(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        $activity->update([
            'content' => [
                'question' => 'What is 2 + 2?',
                'options' => [
                    ['option' => '3'],
                    ['option' => '4'],
                    ['option' => '5'],
                ],
                // Intentionally omit the answer index.
            ],
        ]);

        $xpBefore = (int) $child->fresh()->xp;

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity->fresh(),
            ])
            ->call('submitOption', 0)
            ->assertStatus(422);

        $this->assertDatabaseMissing('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
        ]);

        $this->assertSame($xpBefore, (int) $child->fresh()->xp);
    }

    public function test_quiz_with_out_of_range_answer_is_rejected(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();

        $activity->update([
            'content' => [
                'question' => 'What is 2 + 2?',
                'options' => [
                    ['option' => '3'],
                    ['option' => '4'],
                    ['option' => '5'],
                ],
                'answer' => 99,
            ],
        ]);

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity->fresh(),
            ])
            ->call('submitOption', 0)
            ->assertStatus(422);

        $this->assertDatabaseMissing('activity_progress', [
            'child_id' => $child->id,
            'activity_id' => $activity->id,
        ]);
    }

    public function test_generic_completion_remains_available_for_non_quiz_activities(): void
    {
        [$user, $child] = $this->createChild();

        [, , , $activity] = $this->createActivity();
        $activity->update([
            'activity_type' => 'story',
            'content' => [
                'instructions' => 'Read the story, then complete it.',
            ],
        ]);

        Livewire::actingAs($user)
            ->test(Activity::class, [
                'activity' => $activity->fresh(),
            ])
            ->call('complete')
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

    public function test_child_cannot_access_activity_from_another_course_by_guessing_id(): void
    {
        [$user, $child] = $this->createChild();

        // The second course is created first. Course A is activated last
        // so it remains the child's current journey.
        [, , , $activityB] = $this->createActivity();
        [$courseA, , , $activityA] = $this->createActivity();

        $this->assertNotSame(
            $activityA->lesson->module->course_id,
            $activityB->lesson->module->course_id
        );

        // Ensure course A is the current course independent of timestamp
        // precision in the test database.
        CourseProgress::query()
            ->where('child_id', $child->id)
            ->where('course_id', $courseA->id)
            ->update(['updated_at' => now()->addMinute()]);

        $this->actingAs($user)
            ->get("/learn/activity/{$activityB->id}")
            ->assertNotFound();
    }

    public function test_child_cannot_skip_to_a_later_activity(): void
    {
        [$user] = $this->createChild();

        [, , $lesson, $firstActivity] = $this->createActivity();

        $laterActivity = LessonActivity::factory()->create([
            'lesson_id' => $lesson->id,
            'position' => 2,
            'is_published' => true,
            'activity_type' => 'quiz',
        ]);

        $this->assertNotSame($firstActivity->id, $laterActivity->id);

        $this->actingAs($user)
            ->get("/learn/activity/{$laterActivity->id}")
            ->assertNotFound();
    }

    public function test_completed_activity_can_be_reviewed_when_another_course_is_current(): void
    {
        [$user, $child] = $this->createChild();

        [$previousCourse, , , $completedActivity] = $this->createActivity();

        $child->activityProgress()->create([
            'activity_id' => $completedActivity->id,
            'completed' => true,
            'score' => 100,
            'xp_earned' => 10,
            'completed_at' => now(),
        ]);

        // A later-created progress row becomes the active course.
        [$currentCourse] = $this->createActivity();
        CourseProgress::query()
            ->where('child_id', $child->id)
            ->where('course_id', $currentCourse->id)
            ->update(['updated_at' => now()->addMinute()]);

        $this->assertNotSame($previousCourse->id, $currentCourse->id);

        $this->actingAs($user)
            ->get("/learn/activity/{$completedActivity->id}")
            ->assertOk()
            ->assertSee($completedActivity->title);
    }

    public function test_guest_cannot_access_activity(): void
    {
        [, , , $activity] = $this->createActivity();

        $this->get("/learn/activity/{$activity->id}")
            ->assertRedirect("/login");
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