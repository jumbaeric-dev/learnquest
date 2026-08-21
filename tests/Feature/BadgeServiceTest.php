<?php

namespace Tests\Feature;

use App\Models\Badge;
use App\Models\Child;
use App\Models\ChildStreak;
use App\Services\BadgeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BadgeServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BadgeService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BadgeService::class);
    }

    public function test_eligible_xp_badge_is_awarded(): void
    {
        $child = Child::factory()->create([
            'xp' => 100,
        ]);

        $badge = Badge::factory()->create([
            'name' => 'XP 100',
            'slug' => 'xp-100',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(1, $awarded);
        $this->assertSame($badge->id, $awarded[0]->id);

        $this->assertTrue(
            $this->service->has($child->fresh(), 'xp-100')
        );
    }

    public function test_ineligible_xp_badge_is_not_awarded(): void
    {
        $child = Child::factory()->create([
            'xp' => 99,
        ]);

        Badge::factory()->create([
            'slug' => 'xp-100',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(0, $awarded);
        $this->assertDatabaseCount('badge_child', 0);
    }

    public function test_eligible_level_badge_is_awarded(): void
    {
        $child = Child::factory()->create([
            'level' => 5,
        ]);

        $badge = Badge::factory()->create([
            'name' => 'Level 5',
            'slug' => 'level-5',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(1, $awarded);
        $this->assertSame($badge->id, $awarded[0]->id);
    }

    public function test_eligible_streak_badge_is_awarded(): void
    {
        $child = Child::factory()->create();

        ChildStreak::create([
            'child_id' => $child->id,
            'current_streak' => 7,
            'longest_streak' => 7,
        ]);

        $badge = Badge::factory()->create([
            'name' => '7 Day Streak',
            'slug' => 'streak-7',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(1, $awarded);
        $this->assertSame($badge->id, $awarded[0]->id);
    }

    public function test_inactive_badge_is_not_awarded(): void
    {
        $child = Child::factory()->create([
            'xp' => 1000,
        ]);

        Badge::factory()->create([
            'slug' => 'xp-100',
            'is_active' => false,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(0, $awarded);
        $this->assertDatabaseCount('badge_child', 0);
    }

    public function test_existing_badge_is_not_awarded_again(): void
    {
        $child = Child::factory()->create([
            'xp' => 100,
        ]);

        $badge = Badge::factory()->create([
            'slug' => 'xp-100',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        $child->awardBadge($badge);

        $xpBefore = $child->fresh()->xp;

        $awarded = $this->service->evaluate($child->fresh());

        $this->assertCount(0, $awarded);

        $this->assertSame(
            $xpBefore,
            $child->fresh()->xp
        );

        $this->assertDatabaseCount('badge_child', 1);
    }

    public function test_multiple_eligible_badges_are_awarded(): void
    {
        $child = Child::factory()->create([
            'xp' => 500,
            'level' => 5,
        ]);

        ChildStreak::create([
            'child_id' => $child->id,
            'current_streak' => 7,
            'longest_streak' => 7,
        ]);

        Badge::factory()->create([
            'slug' => 'xp-100',
            'xp_reward' => 10,
            'is_active' => true,
        ]);

        Badge::factory()->create([
            'slug' => 'level-5',
            'xp_reward' => 20,
            'is_active' => true,
        ]);

        Badge::factory()->create([
            'slug' => 'streak-7',
            'xp_reward' => 30,
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(3, $awarded);

        $this->assertDatabaseCount('badge_child', 3);
    }

    public function test_badge_xp_is_awarded_when_badge_is_earned(): void
    {
        $child = Child::factory()->create([
            'xp' => 100,
        ]);

        Badge::factory()->create([
            'slug' => 'xp-100',
            'xp_reward' => 25,
            'is_active' => true,
        ]);

        $this->service->evaluate($child);

        $this->assertSame(
            125,
            $child->fresh()->xp
        );
    }

    public function test_unknown_badge_slug_is_ignored(): void
    {
        $child = Child::factory()->create([
            'xp' => 1000,
            'level' => 10,
        ]);

        Badge::factory()->create([
            'slug' => 'special-achievement',
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertCount(0, $awarded);
        $this->assertDatabaseCount('badge_child', 0);
    }

    public function test_evaluate_returns_empty_array_when_no_badges_are_eligible(): void
    {
        $child = Child::factory()->create([
            'xp' => 0,
            'level' => 1,
        ]);

        Badge::factory()->create([
            'slug' => 'xp-100',
            'is_active' => true,
        ]);

        Badge::factory()->create([
            'slug' => 'level-5',
            'is_active' => true,
        ]);

        $awarded = $this->service->evaluate($child);

        $this->assertIsArray($awarded);
        $this->assertEmpty($awarded);
    }

    public function test_earned_returns_child_badges(): void
    {
        $child = Child::factory()->create();

        $badge = Badge::factory()->create([
            'slug' => 'xp-100',
        ]);

        $child->awardBadge($badge);

        $earned = $this->service->earned($child->fresh());

        $this->assertCount(1, $earned);
        $this->assertSame($badge->id, $earned->first()->id);
    }

    public function test_has_returns_true_for_earned_badge(): void
    {
        $child = Child::factory()->create();

        $badge = Badge::factory()->create([
            'slug' => 'xp-100',
        ]);

        $child->awardBadge($badge);

        $this->assertTrue(
            $this->service->has(
                $child->fresh(),
                'xp-100'
            )
        );
    }

    public function test_has_returns_false_for_unearned_badge(): void
    {
        $child = Child::factory()->create();

        Badge::factory()->create([
            'slug' => 'xp-100',
        ]);

        $this->assertFalse(
            $this->service->has(
                $child,
                'xp-100'
            )
        );
    }
}