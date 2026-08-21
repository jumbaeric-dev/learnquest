<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\ChildStreak;
use App\Services\StreakService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StreakServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_activity_starts_streak_at_one(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        $streak = app(StreakService::class)
            ->recordActivity($child);

        $this->assertSame(1, $streak->current_streak);
        $this->assertSame(1, $streak->longest_streak);
        $this->assertSame(
            '2026-08-19',
            $streak->last_activity_date->format('Y-m-d')
        );

        $this->assertDatabaseHas('child_streaks', [
            'child_id' => $child->id,
            'current_streak' => 1,
            'longest_streak' => 1,
        ]);

        Carbon::setTestNow();
    }

    public function test_repeating_activity_on_same_day_does_not_increase_streak(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        $service = app(StreakService::class);

        $service->recordActivity($child);

        Carbon::setTestNow('2026-08-19 20:00:00');

        $streak = $service->recordActivity($child);

        $this->assertSame(1, $streak->current_streak);
        $this->assertSame(1, $streak->longest_streak);
        $this->assertSame(
            '2026-08-19',
            $streak->last_activity_date->format('Y-m-d')
        );

        Carbon::setTestNow();
    }

    public function test_activity_on_next_day_increments_streak(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        $service = app(StreakService::class);

        $service->recordActivity($child);

        Carbon::setTestNow('2026-08-20 10:00:00');

        $streak = $service->recordActivity($child);

        $this->assertSame(2, $streak->current_streak);
        $this->assertSame(2, $streak->longest_streak);
        $this->assertSame(
            '2026-08-20',
            $streak->last_activity_date->format('Y-m-d')
        );

        Carbon::setTestNow();
    }

    public function test_broken_streak_resets_to_one(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        $service = app(StreakService::class);

        $service->recordActivity($child);

        Carbon::setTestNow('2026-08-20 10:00:00');
        $service->recordActivity($child);

        Carbon::setTestNow('2026-08-22 10:00:00');

        $streak = $service->recordActivity($child);

        $this->assertSame(1, $streak->current_streak);

        // The historical longest streak must be preserved.
        $this->assertSame(2, $streak->longest_streak);

        $this->assertSame(
            '2026-08-22',
            $streak->last_activity_date->format('Y-m-d')
        );

        Carbon::setTestNow();
    }

    public function test_longest_streak_is_preserved_after_reset(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        $streak = ChildStreak::create([
            'child_id' => $child->id,
            'current_streak' => 5,
            'longest_streak' => 10,
            'last_activity_date' => '2026-08-18',
        ]);

        $result = app(StreakService::class)
            ->recordActivity($child);

        $this->assertSame(6, $result->current_streak);
        $this->assertSame(10, $result->longest_streak);

        Carbon::setTestNow();
    }

    public function test_longest_streak_increases_when_current_streak_exceeds_it(): void
    {
        Carbon::setTestNow('2026-08-19 10:00:00');

        $child = Child::factory()->create();

        ChildStreak::create([
            'child_id' => $child->id,
            'current_streak' => 3,
            'longest_streak' => 3,
            'last_activity_date' => '2026-08-18',
        ]);

        $streak = app(StreakService::class)
            ->recordActivity($child);

        $this->assertSame(4, $streak->current_streak);
        $this->assertSame(4, $streak->longest_streak);

        Carbon::setTestNow();
    }

    public function test_current_returns_zero_when_child_has_no_streak(): void
    {
        $child = Child::factory()->create();

        $this->assertSame(
            0,
            app(StreakService::class)->current($child)
        );
    }

    public function test_current_returns_current_streak(): void
    {
        $child = Child::factory()->create();

        ChildStreak::create([
            'child_id' => $child->id,
            'current_streak' => 7,
            'longest_streak' => 10,
            'last_activity_date' => '2026-08-19',
        ]);

        $this->assertSame(
            7,
            app(StreakService::class)->current($child)
        );
    }
}