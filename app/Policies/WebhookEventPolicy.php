<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookEvent;

class WebhookEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payments');
    }

    public function view(User $user, WebhookEvent $event): bool
    {
        return $user->can('view payments');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, WebhookEvent $event): bool
    {
        return false;
    }

    public function delete(User $user, WebhookEvent $event): bool
    {
        return false;
    }
}