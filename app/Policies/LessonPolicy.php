<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage lessons') || $user->can('view analytics');
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage lessons');
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->can('manage lessons');
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->can('manage lessons');
    }

    public function restore(User $user, Lesson $lesson): bool
    {
        return $user->can('manage lessons');
    }

    public function forceDelete(User $user, Lesson $lesson): bool
    {
        return $user->can('manage lessons');
    }
}
