<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage courses') || $user->can('view analytics');
    }

    public function view(User $user, Course $course): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage courses');
    }

    public function update(User $user, Course $course): bool
    {
        return $user->can('manage courses');
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->can('manage courses');
    }

    public function restore(User $user, Course $course): bool
    {
        return $user->can('manage courses');
    }

    public function forceDelete(User $user, Course $course): bool
    {
        return $user->can('manage courses');
    }
}
