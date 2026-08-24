<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view analytics') || $user->can('manage users');
    }

    public function view(User $user, Child $child): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage users');
    }

    public function update(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }

    public function delete(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }

    public function restore(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }

    public function forceDelete(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }
}
