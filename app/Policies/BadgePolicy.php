<?php

namespace App\Policies;

use App\Models\Badge;
use App\Models\User;

class BadgePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->can('view analytics');
    }

    public function view(User $user, Badge $badge): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Badge $badge): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Badge $badge): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Badge $badge): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Badge $badge): bool
    {
        return $user->isAdmin();
    }
}
