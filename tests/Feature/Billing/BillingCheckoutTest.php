<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillingCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        Role::findOrCreate('child', 'web');

        $this->seed(PlanSeeder::class);

        config([
            'learnquest.gateways.default_provider' => 'intasend',
            'app.url' => 'https://learnquest.test',
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.driver' =>
            \App\Services\Billing\Gateways\IntaSendGateway::class,
            'learnquest.gateways.providers.intasend.currencies' => ['KES'],
            'learnquest.gateways.providers.intasend.public_key' => 'test-public-key',
            'learnquest.gateways.providers.intasend.secret_key' => 'test-secret-key',
            'learnquest.gateways.providers.intasend.base_url' => 'https://api.intasend.com',
        ]);
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    public function test_parent_can_start_intasend_hosted_checkout(): void
    {
        $parent = $this->parentAccount();
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();

        Http::fake([
            'api.intasend.com/api/v1/checkout/' => Http::response([
                'invoice' => ['invoice_id' => 'test-invoice-123'],
                'url' => 'https://payment.intasend.com/checkout/test-invoice-123',
            ], 200),
        ]);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->call('startCheckout', $price->id)
            ->assertRedirect('https://payment.intasend.com/checkout/test-invoice-123');

        $payment = Payment::query()->where('user_id', $parent->id)->sole();

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('intasend', $payment->provider);
        $this->assertSame($price->id, $payment->plan_price_id);
        $this->assertSame(
            'test-invoice-123',
            data_get($payment->metadata, 'intasend_invoice_id')
        );
        $this->assertSame(
            'https://payment.intasend.com/checkout/test-invoice-123',
            data_get($payment->metadata, 'intasend_checkout_url')
        );

        Http::assertSentCount(1);
    }

    public function test_repeated_checkout_action_reuses_the_existing_hosted_url(): void
    {
        $parent = $this->parentAccount();
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();

        Http::fake([
            'api.intasend.com/api/v1/checkout/' => Http::response([
                'invoice' => ['invoice_id' => 'test-invoice-repeat'],
                'url' => 'https://payment.intasend.com/checkout/test-invoice-repeat',
            ], 200),
        ]);

        $component = Livewire::actingAs($parent)->test(Index::class);

        $component->call('startCheckout', $price->id)
            ->assertRedirect('https://payment.intasend.com/checkout/test-invoice-repeat');

        $component->call('startCheckout', $price->id)
            ->assertRedirect('https://payment.intasend.com/checkout/test-invoice-repeat');

        $this->assertSame(1, Payment::where('user_id', $parent->id)->count());
        Http::assertSentCount(1);
    }

    public function test_inactive_price_cannot_be_purchased(): void
    {
        $parent = $this->parentAccount();
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();

        $price->update(['is_active' => false]);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->call('startCheckout', $price->id)
            ->assertHasErrors(['checkout']);

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
    }

    public function test_non_parent_cannot_access_billing_checkout(): void
    {
        $user = User::factory()->create();

        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();

        $this->actingAs($user)
            ->get(route('parent.billing'))
            ->assertForbidden();

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
    }

    public function test_gateway_failure_does_not_mark_payment_as_failed(): void
    {
        $parent = $this->parentAccount();
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->firstOrFail();

        Http::fake([
            'api.intasend.com/api/v1/checkout/' => Http::response([
                'message' => 'Temporary provider error',
            ], 503),
        ]);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->call('startCheckout', $price->id)
            ->assertHasErrors(['checkout']);

        $payment = Payment::query()->where('user_id', $parent->id)->sole();

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertNull($payment->paid_at);
        $this->assertNull($payment->failed_at);
    }

    public function test_parent_can_start_paystack_checkout_when_it_is_the_default_provider(): void
    {
        config([
            'learnquest.gateways.default_provider' => 'paystack',
            'learnquest.gateways.providers.paystack.enabled' => true,
            'learnquest.gateways.providers.paystack.currencies' => ['KES'],
            'learnquest.gateways.providers.paystack.secret_key' => 'sk_test_learnquest_test_key',
        ]);

        $parent = $this->parentAccount();
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();

        // The provider must echo the exact internal reference generated for this payment.
        Http::fake(function ($request) {
            return Http::response([
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test-session',
                    'access_code' => 'test-access-code',
                    'reference' => $request['reference'],
                ],
            ]);
        });

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->call('startCheckout', $price->id)
            ->assertRedirect('https://checkout.paystack.com/test-session');

        $payment = Payment::query()->where('user_id', $parent->id)->sole();
        $this->assertSame('paystack', $payment->provider);
        $this->assertSame($payment->reference, data_get($payment->metadata, 'paystack_transaction_reference'));
        $this->assertSame('https://checkout.paystack.com/test-session', data_get($payment->metadata, 'paystack_checkout_url'));
    }

}
