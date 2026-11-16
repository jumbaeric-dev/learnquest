<?php

namespace App\Console\Commands;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Services\Billing\PaymentReconciliationService;
use Illuminate\Console\Command;
use Illuminate\Validation\ValidationException;

class ReconcilePayments extends Command
{
    protected $signature = 'learnquest:reconcile-payments {--hours=48 : Only check payments created within this many hours}';

    protected $description = 'Re-check unsettled gateway payments with their providers, settle confirmed ones, and close abandoned attempts.';

    public function handle(PaymentReconciliationService $reconciler): int
    {
        $checked = 0;
        $settled = 0;

        $enabledProviders = collect((array) config('learnquest.gateways.providers', []))
            ->filter(fn ($settings) => is_array($settings) && ($settings['enabled'] ?? false))
            ->keys()
            ->all();

        $payments = Payment::query()
            ->pending()
            ->whereIn('provider', $enabledProviders)
            ->where('created_at', '>=', now()->subHours(max(1, (int) $this->option('hours'))))
            ->orderBy('id')
            ->limit(200)
            ->get();

        foreach ($payments as $payment) {
            try {
                $result = $reconciler->reconcile($payment);
            } catch (PaymentGatewayException | ValidationException $exception) {
                $this->warn("{$payment->reference}: {$exception->getMessage()}");

                continue;
            }

            $checked++;

            if (($result['status'] ?? null) === 'succeeded') {
                $settled++;
            }
        }

        // Attempts nobody completed a day after they expired are closed. A late
        // confirmation can still settle them, because the processor honours money received.
        $closed = Payment::query()
            ->pending()
            ->where('expires_at', '<', now()->subDay())
            ->update([
                'status' => Payment::STATUS_CANCELLED,
                'failed_at' => now(),
                'failure_code' => 'abandoned',
            ]);

        $this->info("Checked {$checked}, settled {$settled}, closed {$closed}.");

        return self::SUCCESS;
    }
}