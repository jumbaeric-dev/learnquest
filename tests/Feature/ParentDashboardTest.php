<?php

namespace Tests\Feature;

use App\Models\ActivityProgress;
use App\Models\Badge;
use App\Models\Child;
use App\Models\ChildStreak;
use App\Models\Lesson;
use App\Models\LessonActivity;
use App\Models\LessonProgress;
use App\Models\Course;
use App\Models\CourseProgress;
use App\Models\NovaMessage;
use App\Models\Skill;
use App\Models\User;
use App\Services\ParentPortal\ParentDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Carbon::setTestNow(Carbon::parse("2026-10-30 12:00:00"));

        Role::findOrCreate("parent", "web");
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    protected function makeParent(): User
    {
        $user = User::factory()->create();
        $user->assignRole("parent");

        return $user;
    }

    protected function makeChild(User $parent, array $attributes = []): Child
    {
        return Child::factory()->create(
            array_merge(["parent_id" => $parent->id], $attributes)
        );
    }

    protected function completeActivity(Child $child, Carbon $at, int $xp = 25): void
    {
        ActivityProgress::create([
            "child_id" => $child->id,
            "activity_id" => LessonActivity::factory()->create()->id,
            "completed" => true,
            "xp_earned" => $xp,
            "completed_at" => $at,
        ]);
    }

    public function test_overview_only_contains_the_parents_own_children(): void
    {
        $parent = $this->makeParent();
        $other = $this->makeParent();

        $mine = $this->makeChild($parent, ["first_name" => "Amina"]);
        $this->makeChild($other, ["first_name" => "Zuberi"]);

        $overview = app(ParentDashboardService::class)->overview($parent);

        $this->assertSame([$mine->id], array_column($overview["children"], "id"));
        $this->assertSame(1, $overview["family"]["children"]);
    }

    public function test_dashboard_page_never_shows_another_familys_children(): void
    {
        $parent = $this->makeParent();
        $other = $this->makeParent();

        $this->makeChild($parent, ["first_name" => "Amina", "last_name" => "Otieno"]);
        $this->makeChild($other, ["first_name" => "Zuberi", "last_name" => "Mwangi"]);

        $this->actingAs($parent)
            ->get(route("parent.dashboard"))
            ->assertOk()
            ->assertSee("Amina Otieno")
            ->assertDontSee("Zuberi");
    }

    public function test_parent_with_no_children_sees_an_empty_state(): void
    {
        $parent = $this->makeParent();

        $overview = app(ParentDashboardService::class)->overview($parent);

        $this->assertSame([], $overview["children"]);

        $this->actingAs($parent)
            ->get(route("parent.dashboard"))
            ->assertOk()
            ->assertSee("No explorers yet");
    }

    public function test_weekly_stats_only_count_recent_completed_activity(): void
    {
        $parent = $this->makeParent();
        $child = $this->makeChild($parent);

        $this->completeActivity($child, now()->subHour(), 25);
        $this->completeActivity($child, now()->subDays(10), 40);

        $summary = app(ParentDashboardService::class)->overview($parent)["children"][0];

        $this->assertSame(1, $summary["week"]["activities"]);
        $this->assertSame(25, $summary["week"]["xp"]);
        $this->assertSame(1, $summary["week"]["active_days"]);
        $this->assertSame("Today", $summary["last_active_label"]);
    }

    public function test_family_summary_aggregates_weekly_xp_lessons_badges_and_distinct_active_days(): void
    {
        $parent = $this->makeParent();
        $firstChild = $this->makeChild($parent);
        $secondChild = $this->makeChild($parent);

        $this->completeActivity($firstChild, now(), 25);
        $this->completeActivity($firstChild, now()->subDay(), 15);
        $this->completeActivity($secondChild, now()->subDay(), 35);

        foreach ([$firstChild, $secondChild] as $child) {
            LessonProgress::create([
                'child_id' => $child->id,
                'lesson_id' => Lesson::factory()->create()->id,
                'completed_activities' => 1,
                'total_activities' => 1,
                'progress_percentage' => 100,
                'completed' => true,
                'completed_at' => now(),
            ]);

            $badge = Badge::factory()->create();
            $child->badges()->attach($badge->id, [
                'earned_at' => now(),
            ]);
        }

        $family = app(ParentDashboardService::class)->overview($parent)['family'];

        $this->assertSame(3, $family['activities']);
        $this->assertSame(2, $family['lessons']);
        $this->assertSame(2, $family['badges']);
        $this->assertSame(75, $family['xp']);
        // Count distinct dates across the family, not a sum per child.
        $this->assertSame(2, $family['active_days']);
    }

    public function test_a_stale_streak_is_shown_as_zero(): void
    {
        $parent = $this->makeParent();
        $child = $this->makeChild($parent);

        $streak = ChildStreak::create([
            "child_id" => $child->id,
            "current_streak" => 5,
            "longest_streak" => 9,
            "last_activity_date" => now()->subDays(3),
        ]);

        $service = app(ParentDashboardService::class);

        $this->assertSame(0, $service->overview($parent)["children"][0]["streak"]["current"]);
        $this->assertSame(9, $service->overview($parent)["children"][0]["streak"]["longest"]);

        $streak->update(["last_activity_date" => now()->subDay()]);

        $this->assertSame(5, $service->overview($parent)["children"][0]["streak"]["current"]);
    }

    public function test_child_policy_limits_parents_to_their_own_children(): void
    {
        Permission::findOrCreate("view analytics", "web");
        Role::findByName("parent")->givePermissionTo("view analytics");

        $parent = $this->makeParent();
        $other = $this->makeParent();

        $own = $this->makeChild($parent);
        $theirs = $this->makeChild($other);

        $this->assertTrue($parent->can("view", $own));
        $this->assertFalse($parent->can("view", $theirs));
        $this->assertFalse($parent->can("update", $theirs));
        $this->assertFalse($parent->can("delete", $theirs));
    }

    public function test_parent_can_view_detailed_report_for_their_child(): void
    {
        // This test verifies report rendering, not the Premium feature gate.
        config(['learnquest.billing.enforced' => false]);
        $parent = $this->makeParent();
        $child = $this->makeChild($parent, [
            "first_name" => "Amina",
            "last_name" => "Otieno",
            "username" => "amina",
        ]);

        $course = Course::factory()->create(["title" => "Money Explorers"]);
        CourseProgress::create([
            "child_id" => $child->id,
            "course_id" => $course->id,
            "completed_lessons" => 1,
            "total_lessons" => 4,
            "progress_percentage" => 25,
            "completed" => false,
        ]);

        $skill = Skill::factory()->create(["name" => "Decision Making"]);
        $child->skillProgress()->create([
            "skill_id" => $skill->id,
            "xp" => 50,
            "level" => 1,
            "progress_percentage" => 45,
        ]);

        $this->completeActivity($child, now()->subHour());
        NovaMessage::create([
            "child_id" => $child->id,
            "role" => "user",
            "content" => "A private Nova prompt that must not appear in parent reports.",
        ]);

        $this->actingAs($parent)
            ->get(route("parent.children.show", $child->id))
            ->assertOk()
            ->assertSee("Amina Otieno")
            ->assertSee("Money Explorers")
            ->assertSee("Decision Making")
            ->assertSee("Nova prompts this week")
            ->assertDontSee("A private Nova prompt");
    }

    public function test_parent_cannot_open_another_familys_child_detail(): void
    {
        $parent = $this->makeParent();
        $otherParent = $this->makeParent();
        $otherChild = $this->makeChild($otherParent, ["first_name" => "Private"]);

        $this->actingAs($parent)
            ->get(route("parent.children.show", $otherChild->id))
            ->assertNotFound();
    }

    public function test_child_detail_service_returns_nova_counts_without_message_content(): void
    {
        $parent = $this->makeParent();
        $child = $this->makeChild($parent);

        NovaMessage::create([
            "child_id" => $child->id,
            "role" => "user",
            "content" => "Sensitive conversation text",
        ]);
        NovaMessage::create([
            "child_id" => $child->id,
            "role" => "assistant",
            "content" => "Private assistant response",
        ]);

        $report = app(ParentDashboardService::class)->childDetails($parent, $child->id);

        $this->assertSame(1, $report["nova"]["messages_this_week"]);
        $this->assertArrayNotHasKey("content", $report["nova"]);
        $this->assertArrayNotHasKey("messages", $report["nova"]);
    }

    public function test_staff_with_analytics_access_can_still_view_children(): void
    {
        Permission::findOrCreate("view analytics", "web");

        $teacher = User::factory()->create();
        $teacher->givePermissionTo("view analytics");

        $child = $this->makeChild($this->makeParent());

        $this->assertTrue($teacher->can("view", $child));
    }
}
