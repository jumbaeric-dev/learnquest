<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Billing\EntitlementService;
use App\Services\Billing\SubscriptionService;
use Illuminate\Console\Command;

class StartTrials extends Command
{
    protected $signature = 'learnquest:start-trials';

    protected $description = 'Start the free Premium trial for existing parents who have never had a subscription (run once when billing launches).';

    public function handle(
        EntitlementService $entitlements,
        SubscriptionService $subscriptions
    ): int {
        if (! $entitlements->enforced()) {
            $this->error('Billing is not enforced. Set LEARNQUEST_BILLING_ENFORCED=true first, so trials do not run out before launch.');

            return self::FAILURE;
        }

        $started = 0;

        User::role('parent')->cursor()->each(function (User $parent) use ($subscriptions, &$started) {
            if ($subscriptions->startTrialIfEligible($parent)) {
                $started++;
            }
        });

        $this->info("Started {$started} trials.");

        return self::SUCCESS;
    }
}