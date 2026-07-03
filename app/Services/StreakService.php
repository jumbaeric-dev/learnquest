<?php

namespace App\Services;

use App\Models\Child;
use App\Models\ChildStreak;
use Carbon\Carbon;

class StreakService
{
    public function recordActivity(Child $child): ChildStreak
    {
        $streak = ChildStreak::firstOrCreate(
            ['child_id' => $child->id],
            [
                'current_streak' => 0,
                'longest_streak' => 0,
            ]
        );

        $today = Carbon::today();

        // First ever activity
        if (! $streak->last_activity_date) {
            return $this->setInitialStreak($streak, $today);
        }

        $lastActivity = Carbon::parse($streak->last_activity_date);

        // Already recorded today
        if ($lastActivity->isSameDay($today)) {
            return $streak;
        }

        // Consecutive day
        if ($lastActivity->copy()->addDay()->isSameDay($today)) {
            return $this->incrementStreak($streak, $today);
        }

        // Broken streak
        return $this->resetStreak($streak, $today);
    }

    protected function setInitialStreak(ChildStreak $streak, Carbon $today): ChildStreak
    {
        $streak->update([
            'current_streak' => 1,
            'longest_streak' => 1,
            'last_activity_date' => $today,
        ]);

        return $streak->fresh();
    }

    protected function incrementStreak(ChildStreak $streak, Carbon $today): ChildStreak
    {
        $newStreak = $streak->current_streak + 1;

        $streak->update([
            'current_streak' => $newStreak,
            'longest_streak' => max($newStreak, $streak->longest_streak),
            'last_activity_date' => $today,
        ]);

        return $streak->fresh();
    }

    protected function resetStreak(ChildStreak $streak, Carbon $today): ChildStreak
    {
        $streak->update([
            'current_streak' => 1,
            'last_activity_date' => $today,
        ]);

        return $streak->fresh();
    }

    /**
     * 🔥 ADD THIS (needed for BadgeService)
     */
    public function current(Child $child): int
    {
        return ChildStreak::where('child_id', $child->id)
            ->value('current_streak') ?? 0;
    }
}