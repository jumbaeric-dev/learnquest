<?php

namespace App\Services;

use App\Models\Child;

class XPService
{
    /**
     * Award XP to a child.
     */
    public function award(
        Child $child,
        int $xp
    ): Child {
        $child->increment('xp', $xp);

        return $child->refresh();
    }

    /**
     * Remove XP.
     */
    public function remove(
        Child $child,
        int $xp
    ): Child {
        $child->xp = max(
            0,
            $child->xp - $xp
        );

        $child->save();

        return $child->refresh();
    }

    /**
     * Set XP directly.
     */
    public function set(
        Child $child,
        int $xp
    ): Child {
        $child->update([
            'xp' => max(0, $xp),
        ]);

        return $child->refresh();
    }

    /**
     * Get current XP.
     */
    public function current(
        Child $child
    ): int {
        return $child->xp;
    }

    /**
     * Has learner reached a minimum XP?
     */
    public function hasAtLeast(
        Child $child,
        int $xp
    ): bool {
        return $child->xp >= $xp;
    }

    /**
     * Reset XP.
     */
    public function reset(
        Child $child
    ): Child {
        $child->update([
            'xp' => 0,
        ]);

        return $child->refresh();
    }

    public function awardForActivity(Child $child): Child
    {
        return $this->award(
            $child,
            config('learnquest.activity_completion_xp')
        );
    }
}
