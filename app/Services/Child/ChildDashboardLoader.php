<?php

namespace App\Services\Child;

use App\Models\Child;

class ChildDashboardLoader
{
    public function load(
        Child $child
    ): Child {
        return Child::query()
            ->whereKey($child->id)
            ->with([
                'streak',
                'badges',
                'skillProgress.skill',
                'courseProgress.course',
                'lessonProgress.lesson',
                'activityProgress.activity',
            ])
            ->firstOrFail();
    }
}
