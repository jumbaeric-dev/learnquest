<?php

namespace App\Policies;

use App\Models\Subject;
use App\Models\User;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage subjects') || $user->can('view analytics');
    }

    public function view(User $user, Subject $subject): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage subjects');
    }

    public function update(User $user, Subject $subject): bool
    {
        return $user->can('manage subjects');
    }

    public function delete(User $user, Subject $subject): bool
    {
        return $user->can('manage subjects');
    }

    public function restore(User $user, Subject $subject): bool
    {
        return $user->can('manage subjects');
    }

    public function forceDelete(User $user, Subject $subject): bool
    {
        return $user->can('manage subjects');
    }
}
