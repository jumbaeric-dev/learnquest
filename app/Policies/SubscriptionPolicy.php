<?php

namespace App\Policies;

use App\Models\Subscription;
use App\Models\User;

/** Subscriptions are read-only in the admin. Changes go through the billing services. */
class SubscriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payments');
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->can('view payments');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return false;
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Subscription $subscription): bool
    {
        return false;
    }

    public function forceDelete(User $user, Subscription $subscription): bool
    {
        return false;
    }
}