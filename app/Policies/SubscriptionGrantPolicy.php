<?php

namespace App\Policies;

use App\Models\SubscriptionGrant;
use App\Models\User;

class SubscriptionGrantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payments');
    }

    public function view(User $user, SubscriptionGrant $grant): bool
    {
        return $user->can('view payments');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, SubscriptionGrant $grant): bool
    {
        return false;
    }

    public function delete(User $user, SubscriptionGrant $grant): bool
    {
        return false;
    }
}