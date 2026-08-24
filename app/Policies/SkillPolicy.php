<?php

namespace App\Policies;

use App\Models\Skill;
use App\Models\User;

class SkillPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage subjects') || $user->can('view analytics');
    }

    public function view(User $user, Skill $skill): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage subjects');
    }

    public function update(User $user, Skill $skill): bool
    {
        return $user->can('manage subjects');
    }

    public function delete(User $user, Skill $skill): bool
    {
        return $user->can('manage subjects');
    }

    public function restore(User $user, Skill $skill): bool
    {
        return $user->can('manage subjects');
    }

    public function forceDelete(User $user, Skill $skill): bool
    {
        return $user->can('manage subjects');
    }
}
