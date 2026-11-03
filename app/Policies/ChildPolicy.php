<?php

namespace App\Policies;

use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isParent() || $user->can('view analytics') || $user->can('manage users');
    }

    public function view(User $user, Child $child): bool
    {
        return $this->ownsChild($user, $child) || $this->hasStaffAccess($user);
    }

    public function create(User $user): bool
    {
        return $user->isParent() || $user->can('manage users');
    }

    public function update(User $user, Child $child): bool
    {
        return $this->ownsChild($user, $child) || $user->can('manage users');
    }

    public function delete(User $user, Child $child): bool
    {
        return $this->ownsChild($user, $child) || $user->can('manage users');
    }

    public function restore(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }

    public function forceDelete(User $user, Child $child): bool
    {
        return $user->can('manage users');
    }

    /**
     * A parent owns the children whose parent_id is their own user id.
     */
    protected function ownsChild(User $user, Child $child): bool
    {
        return $user->isParent() && (int) $child->parent_id === (int) $user->id;
    }

    /**
     * Staff (teachers, school admins, admins) may view children broadly.
     * The parent role also holds "view analytics", so that permission alone
     * must not grant access to other families' children.
     */
    protected function hasStaffAccess(User $user): bool
    {
        if ($user->can('manage users')) {
            return true;
        }

        return $user->can('view analytics') && ! $user->isParent();
    }
}
