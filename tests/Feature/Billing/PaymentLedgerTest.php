<?php

namespace Tests\Feature\Billing;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\Billing\DTO\VerifiedPaymentWebhook;
use App\Services\Billing\PaymentService;
use App\Services\Billing\PaymentWebhookProcessor;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PaymentLedgerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-03 12:00:00'));
        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');
        return $parent;
    }

    private function premiumPrice(): PlanPrice
    {
        return PlanPrice::query()
            ->whereHas('plan', fn($query) => $query->where('key', 'premium'))
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();
    }

    private function webhook(Payment $payment, array $overrides = []): VerifiedPaymentWebhook
    {
        return new VerifiedPaymentWebhook(
            provider: $overrides['provider'] ?? $payment->provider,
            eventId: $overrides['event_id'] ?? 'evt-' . uniqid(),
            eventType: $overrides['event_type'] ?? 'payment.completed',
            paymentReference: $overrides['reference'] ?? $payment->reference,
            providerTransactionId: $overrides['transaction_id'] ?? 'txn-' . uniqid(),
            amountMinor: $overrides['amount'] ?? $payment->amount_minor,
            currency: $overrides['currency'] ?? $payment->currency,
            outcome: $overrides['outcome'] ?? 'succeeded',
            signatureVerifiedAt: $overrides['verified_at'] ?? now()->toDateTimeImmutable(),
            metadata: ['channel' => 'sandbox'],
        );
    }

    public function test_pending_payment_stores_immutable_price_snapshot(): void
    {
        $parent = $this->parentAccount();
        $price = $this->premiumPrice();

        $payment = app(PaymentService::class)->createPending($parent, $price, 'intasend', 'checkout-001');

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('premium', $payment->plan_key);
        $this->assertSame(1, $payment->duration_months);
        $this->assertSame($price->amount_minor, $payment->amount_minor);
        $this->assertSame('KES', $payment->currency);
        $this->assertStringStartsWith('LQ-', $payment->reference);
        $this->assertNotNull($payment->expires_at);
    }

    public function test_payment_creation_is_idempotent_for_same_parent_and_request(): void
    {
        $parent = $this->parentAccount();
        $service = app(PaymentService::class);
        $price = $this->premiumPrice();

        $first = $service->createPending($parent, $price, 'intasend', 'checkout-002');
        $second = $service->createPending($parent, $price, 'intasend', 'checkout-002');

        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Payment::count());
    }

    public function test_idempotency_key_cannot_be_reused_for_a_different_provider_or_price(): void
    {
        $parent = $this->parentAccount();
        $service = app(PaymentService::class);
        $firstPrice = $this->premiumPrice();
        $service->createPending($parent, $firstPrice, 'intasend', 'checkout-003');

        try {
            $service->createPending($parent, $firstPrice, 'paypal', 'checkout-003');
            $this->fail('Expected an idempotency validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('idempotency_key', $exception->errors());
        }
    }

    public function test_only_parent_accounts_can_create_payment_attempts(): void
    {
        $user = User::factory()->create();

        try {
            app(PaymentService::class)->createPending($user, $this->premiumPrice(), 'paypal');
            $this->fail('Expected a parent-account validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('payment', $exception->errors());
        }
    }

    public function test_only_supported_providers_can_create_payment_attempts(): void
    {
        try {
            app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'unknown');
            $this->fail('Expected a provider validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('provider', $exception->errors());
        }
    }

    public function test_verified_successful_webhook_settles_payment_and_activates_subscription(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');

        $result = app(PaymentWebhookProcessor::class)->process($this->webhook($payment, ['transaction_id' => 'INTA-1001', 'event_id' => 'evt-success-1']));

        $this->assertSame('succeeded', $result['status']);
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
        $this->assertNotNull($payment->fresh()->subscription_id);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
        $this->assertSame(WebhookEvent::STATUS_PROCESSED, $result['event']->status);
    }

    public function test_repeated_webhook_event_is_idempotent(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'paypal');
        $webhook = $this->webhook($payment, ['transaction_id' => 'PP-1001', 'event_id' => 'evt-repeat-1']);
        $processor = app(PaymentWebhookProcessor::class);

        $first = $processor->process($webhook);
        $second = $processor->process($webhook);

        $this->assertSame('succeeded', $first['status']);
        $this->assertSame('duplicate_event', $second['status']);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
        $this->assertSame(1, WebhookEvent::count());
    }

    public function test_distinct_success_events_cannot_extend_same_payment_twice(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');
        $processor = app(PaymentWebhookProcessor::class);

        $processor->process($this->webhook($payment, ['transaction_id' => 'INTA-1002', 'event_id' => 'evt-success-2']));
        $endsAt = $payment->fresh()->subscription->ends_at->copy();
        $second = $processor->process($this->webhook($payment, ['transaction_id' => 'INTA-1002', 'event_id' => 'evt-success-3']));

        $this->assertSame('duplicate_payment', $second['status']);
        $this->assertTrue($endsAt->equalTo($payment->fresh()->subscription->ends_at));
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_amount_mismatch_is_rejected_without_settling_payment(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');

        $result = app(PaymentWebhookProcessor::class)->process($this->webhook($payment, ['amount' => $payment->amount_minor + 1, 'event_id' => 'evt-bad-amount']));

        $this->assertSame('rejected', $result['status']);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertStringContainsString('amount', strtolower($result['event']->failure_reason));
        $this->assertSame(0, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_currency_and_provider_mismatches_are_rejected(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');
        $processor = app(PaymentWebhookProcessor::class);

        $currencyResult = $processor->process($this->webhook($payment, ['currency' => 'USD', 'event_id' => 'evt-bad-currency']));
        $providerResult = $processor->process($this->webhook($payment, ['provider' => 'paypal', 'event_id' => 'evt-bad-provider']));

        $this->assertSame('rejected', $currencyResult['status']);
        $this->assertSame('rejected', $providerResult['status']);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_failed_webhook_marks_payment_failed_without_subscription(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'paypal');

        $result = app(PaymentWebhookProcessor::class)->process($this->webhook($payment, [
            'outcome' => 'failed',
            'transaction_id' => 'PP-FAIL-1',
            'event_id' => 'evt-failed-1',
        ]));

        $this->assertSame('failed', $result['status']);
        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->failed_at);
        $this->assertSame(0, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_late_payment_is_still_honoured_when_the_provider_confirms_it(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');
        $payment->forceFill(['expires_at' => now()->subMinute()])->save();

        $result = app(PaymentWebhookProcessor::class)->process($this->webhook($payment, ['event_id' => 'evt-late-1']));

        $this->assertSame('succeeded', $result['status']);
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
        $this->assertTrue((bool) data_get($payment->fresh()->metadata, 'late_settlement'));
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_confirmed_payment_settles_an_attempt_already_marked_failed(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');
        $payment->forceFill([
            'status' => Payment::STATUS_FAILED,
            'failed_at' => now(),
            'failure_code' => 'provider_payment_failed',
        ])->save();

        $result = app(PaymentWebhookProcessor::class)->process($this->webhook($payment, ['event_id' => 'evt-recover-1']));

        $this->assertSame('succeeded', $result['status']);
        $this->assertNull($payment->fresh()->failed_at);
        $this->assertSame(1, Subscription::where('user_id', $payment->user_id)->count());
    }

    public function test_provider_transaction_cannot_be_reused_for_another_payment(): void
    {
        $parent = $this->parentAccount();
        $service = app(PaymentService::class);
        $first = $service->createPending($parent, $this->premiumPrice(), 'intasend', 'tx-guard-1');
        $second = $service->createPending($parent, $this->premiumPrice(), 'intasend', 'tx-guard-2');
        $processor = app(PaymentWebhookProcessor::class);

        $processor->process($this->webhook($first, ['transaction_id' => 'INTA-REUSED-1', 'event_id' => 'evt-tx-first']));
        $result = $processor->process($this->webhook($second, ['transaction_id' => 'INTA-REUSED-1', 'event_id' => 'evt-tx-second']));

        $this->assertSame('rejected', $result['status']);
        $this->assertSame(Payment::STATUS_PENDING, $second->fresh()->status);
        $this->assertStringContainsString('already linked', $result['event']->failure_reason);
        $this->assertSame(1, Subscription::where('user_id', $parent->id)->count());
    }

    public function test_webhook_metadata_is_allow_listed_and_encrypted(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'paypal');
        $webhook = new VerifiedPaymentWebhook(
            provider: 'paypal',
            eventId: 'evt-metadata-1',
            eventType: 'payment.failed',
            paymentReference: $payment->reference,
            providerTransactionId: 'PP-META-1',
            amountMinor: $payment->amount_minor,
            currency: $payment->currency,
            outcome: 'failed',
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: ['channel' => 'sandbox', 'payment_method' => 'card', 'access_token' => 'must-not-be-stored'],
        );

        $result = app(PaymentWebhookProcessor::class)->process($webhook);
        $stored = $result['event']->fresh()->payload;
        $rawPayload = \Illuminate\Support\Facades\DB::table('webhook_events')->value('payload');

        $this->assertSame(['channel' => 'sandbox', 'payment_method' => 'card'], $stored);
        $this->assertStringNotContainsString('sandbox', $rawPayload);
        $this->assertStringNotContainsString('must-not-be-stored', $rawPayload);
    }

    public function test_unknown_payment_reference_is_rejected_and_audited(): void
    {
        $result = app(PaymentWebhookProcessor::class)->process(new VerifiedPaymentWebhook(
            provider: 'paypal',
            eventId: 'evt-unknown-1',
            eventType: 'payment.completed',
            paymentReference: 'LQ-NOT-FOUND',
            providerTransactionId: 'PP-UNKNOWN',
            amountMinor: 499,
            currency: 'USD',
            outcome: 'succeeded',
            signatureVerifiedAt: now()->toDateTimeImmutable(),
            metadata: ['source' => 'sandbox'],
        ));

        $this->assertSame('rejected', $result['status']);
        $this->assertNull($result['payment']);
        $this->assertSame(WebhookEvent::STATUS_REJECTED, $result['event']->status);
        $this->assertSame(1, WebhookEvent::count());
    }

    public function test_webhook_with_future_verification_timestamp_is_rejected(): void
    {
        $payment = app(PaymentService::class)->createPending($this->parentAccount(), $this->premiumPrice(), 'intasend');

        try {
            app(PaymentWebhookProcessor::class)->process($this->webhook($payment, ['verified_at' => now()->addMinute()->toDateTimeImmutable()]));
            $this->fail('Expected webhook validation error.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('webhook', $exception->errors());
        }
    }
}
