<?php

namespace App\Services\Child\Context;

use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class CurrentChildService
{
    /**
     * Resolve the active learning context for either a child login or a
     * parent previewing one of their own child accounts.
     */
    public function current(): ?Child
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        if ($user instanceof User && $user->isParent()) {
            $childId = session('active_child_id');

            if (!$childId) {
                return null;
            }

            return $user->children()
                ->whereKey($childId)
                ->where('is_active', true)
                ->first();
        }

        return $user->child;
    }

    /**
     * True when the signed-in user is a parent viewing a child's pages.
     *
     * Preview is read-only: any action that would write learning progress
     * (XP, streaks, badges, Nova messages) must check this first.
     */
    public function isParentPreview(): bool
    {
        $user = Auth::user();

        return $user instanceof User && $user->isParent();
    }

    /** Start a parent-owned child preview without changing the login user. */
    public function switchTo(Child $child): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isParent(), 403);
        abort_unless((int) $child->parent_id === (int) $user->id, 403);
        abort_unless($child->is_active, 404);

        session(['active_child_id' => $child->id]);
    }

    /** Leave child preview and return to the parent portal. */
    public function leaveParentPreview(): void
    {
        $user = Auth::user();

        abort_unless($user instanceof User && $user->isParent(), 403);

        session()->forget('active_child_id');
    }
}