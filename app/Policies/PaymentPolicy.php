<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

/** Payments are a read-only ledger: nobody edits or deletes them in the admin. */
class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view payments');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('view payments');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Payment $payment): bool
    {
        return false;
    }

    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }

    /** Ask the provider for the real status and settle the payment if it was paid. */
    public function reconcile(User $user, Payment $payment): bool
    {
        return $user->can('manage payments');
    }
}