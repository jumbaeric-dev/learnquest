<?php

namespace App\Policies;

use App\Models\LessonActivity;
use App\Models\User;

class LessonActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage activities') || $user->can('view analytics');
    }

    public function view(User $user, LessonActivity $lessonActivity): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage activities');
    }

    public function update(User $user, LessonActivity $lessonActivity): bool
    {
        return $user->can('manage activities');
    }

    public function delete(User $user, LessonActivity $lessonActivity): bool
    {
        return $user->can('manage activities');
    }

    public function restore(User $user, LessonActivity $lessonActivity): bool
    {
        return $user->can('manage activities');
    }

    public function forceDelete(User $user, LessonActivity $lessonActivity): bool
    {
        return $user->can('manage activities');
    }
}
