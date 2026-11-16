<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index as BillingIndex;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Support\Money;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillingHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    private function payment(User $parent, string $reference, string $status, array $overrides = []): Payment
    {
        $price = PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn($query) => $query->where('key', 'premium'))
            ->firstOrFail();

        $data = array_merge([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => $reference,
            'provider' => 'paystack',
            'amount_minor' => $price->amount_minor,
            'currency' => 'KES',
            'status' => $status,
            'expires_at' => now()->addMinutes(30),
            'paid_at' => $status === Payment::STATUS_SUCCEEDED ? now() : null,
        ], $overrides);

        // created_at is intentionally not mass assignable on the Payment model.
        // Apply test-specific historical timestamps explicitly instead.
        $createdAt = $data['created_at'] ?? null;
        unset($data['created_at']);

        $payment = Payment::create($data);

        if ($createdAt !== null) {
            $payment->forceFill(['created_at' => $createdAt])->save();
        }

        return $payment;
    }

    public function test_money_formats_kes_and_usd(): void
    {
        $this->assertSame('KSh 499', Money::format(49900, 'KES'));
        $this->assertSame('KSh 1,299.50', Money::format(129950, 'KES'));
        $this->assertSame('$4.99', Money::format(499, 'USD'));
    }

    public function test_parent_sees_their_own_paid_payment(): void
    {
        $parent = $this->parentAccount();
        $payment = $this->payment($parent, 'LQ-HIST-0001', Payment::STATUS_SUCCEEDED);

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('Payment history')
            ->assertSee($payment->formattedAmount())
            ->assertSee('Paid')
            ->assertSee('LQ-HIST-0001');
    }

    public function test_parent_never_sees_another_familys_payments(): void
    {
        $parent = $this->parentAccount();
        $this->payment($this->parentAccount(), 'LQ-OTHER-0001', Payment::STATUS_SUCCEEDED);

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertDontSee('LQ-OTHER-0001')
            ->assertSee('No payments yet');
    }

    public function test_abandoned_and_old_failed_attempts_are_hidden(): void
    {
        $parent = $this->parentAccount();

        $this->payment($parent, 'LQ-HIDE-CANCELLED', Payment::STATUS_CANCELLED);
        $this->payment($parent, 'LQ-HIDE-OLDFAIL', Payment::STATUS_FAILED, ['created_at' => now()->subDays(10)]);
        $this->payment($parent, 'LQ-SHOW-RECENTFAIL', Payment::STATUS_FAILED);

        $this->assertDatabaseHas('payments', [
            'reference' => 'LQ-HIDE-OLDFAIL',
        ]);

        $oldPayment = Payment::where(
            'reference',
            'LQ-HIDE-OLDFAIL'
        )->firstOrFail();

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertDontSee('LQ-HIDE-CANCELLED')
            ->assertDontSee('LQ-HIDE-OLDFAIL')
            ->assertSee('LQ-SHOW-RECENTFAIL')
            ->assertSee('Didn’t go through');
    }

    public function test_a_payment_still_being_confirmed_is_shown_as_confirming(): void
    {
        $parent = $this->parentAccount();
        $this->payment($parent, 'LQ-WAIT-0001', Payment::STATUS_PENDING);

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('Confirming')
            ->assertSee('LQ-WAIT-0001');
    }

    public function test_history_shows_five_rows_until_expanded(): void
    {
        $parent = $this->parentAccount();

        foreach (range(1, 7) as $number) {
            $this->payment($parent, sprintf('LQ-LONG-%03d', $number), Payment::STATUS_SUCCEEDED);
        }

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('LQ-LONG-007')
            ->assertDontSee('LQ-LONG-001')
            ->assertSee('Show all (7)')
            ->call('togglePayments')
            ->assertSee('LQ-LONG-001')
            ->assertSee('Show fewer');
    }
}
