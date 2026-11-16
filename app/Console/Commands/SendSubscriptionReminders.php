<?php

namespace App\Console\Commands;

use App\Services\Billing\EntitlementService;
use App\Services\Billing\SubscriptionReminderService;
use Illuminate\Console\Command;

class SendSubscriptionReminders extends Command
{
    protected $signature = 'learnquest:send-subscription-reminders';

    protected $description = 'Email parents whose trial or plan is about to end, or just ended.';

    public function handle(
        EntitlementService $entitlements,
        SubscriptionReminderService $reminders
    ): int {
        if (! $entitlements->enforced()) {
            $this->warn('Billing is not enforced, so no reminders were sent.');

            return self::SUCCESS;
        }

        $result = $reminders->sendDue();

        $this->info("Sent {$result['sent']}, skipped {$result['skipped']}, failed {$result['failed']}.");

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}