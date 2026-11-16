<?php

namespace App\Filament\Resources\Payments\Actions;

use App\Exceptions\Billing\PaymentGatewayException;
use App\Models\Payment;
use App\Services\Billing\PaymentReconciliationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

/**
 * Asks the provider for the payment's real status and settles it through the
 * normal ledger path. Safe to repeat: settlement is idempotent.
 */
class ReconcilePaymentAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'reconcile';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Check with provider')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('gray')
            ->authorize('reconcile')
            ->visible(fn (Payment $record): bool => in_array(
                $record->status,
                [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING],
                true
            ))
            ->requiresConfirmation()
            ->modalHeading('Check this payment with the provider?')
            ->modalDescription('We ask the payment provider for the payment’s real status and settle it if it was paid. This is safe to repeat.')
            ->action(function (Payment $record): void {
                try {
                    $result = app(PaymentReconciliationService::class)->reconcile($record);
                } catch (PaymentGatewayException | ValidationException $exception) {
                    Notification::make()
                        ->title('Provider check failed')
                        ->body(mb_substr($exception->getMessage(), 0, 200))
                        ->danger()
                        ->send();

                    return;
                }

                $status = $result['status'] ?? 'unknown';

                $notification = Notification::make()->title(match ($status) {
                    'succeeded' => 'Payment confirmed and settled',
                    'failed' => 'The provider reports this payment failed',
                    'pending' => 'Still pending at the provider',
                    'no_invoice' => 'No provider transaction exists for this payment yet',
                    'unchanged' => 'Already settled',
                    default => 'Checked: ' . $status,
                });

                $status === 'succeeded' ? $notification->success() : $notification->warning();

                $notification->send();
            });
    }
}