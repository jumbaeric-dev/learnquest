<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('manage modules') || $user->can('view analytics');
    }

    public function view(User $user, CourseModule $courseModule): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage modules');
    }

    public function update(User $user, CourseModule $courseModule): bool
    {
        return $user->can('manage modules');
    }

    public function delete(User $user, CourseModule $courseModule): bool
    {
        return $user->can('manage modules');
    }

    public function restore(User $user, CourseModule $courseModule): bool
    {
        return $user->can('manage modules');
    }

    public function forceDelete(User $user, CourseModule $courseModule): bool
    {
        return $user->can('manage modules');
    }
}
