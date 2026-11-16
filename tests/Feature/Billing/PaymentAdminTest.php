<?php

namespace Tests\Feature\Billing;

use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Filament\Resources\Payments\Pages\ViewPayment;
use App\Filament\Resources\Payments\RelationManagers\WebhookEventsRelationManager;
use App\Models\Payment;
use App\Models\PlanPrice;
use App\Models\User;
use App\Models\WebhookEvent;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class PaymentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.gateways.providers.paystack.enabled' => true,
            'learnquest.gateways.providers.paystack.secret_key' => 'sk_test_example',
            'learnquest.gateways.providers.paystack.currencies' => ['KES'],
        ]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function payment(
        string $reference,
        string $status = Payment::STATUS_SUCCEEDED,
        array $overrides = []
    ): Payment {
        $parent = $overrides['parent'] ?? $this->userWithRole('parent');
        unset($overrides['parent']);

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
            'metadata' => ['paystack_transaction_reference' => $reference],
        ], $overrides);

        // created_at is not mass assignable on the Payment model.
        $createdAt = $data['created_at'] ?? null;
        unset($data['created_at']);

        $payment = Payment::create($data);

        if ($createdAt !== null) {
            $payment->forceFill([
                'created_at' => $createdAt,
            ])->save();
        }

        return $payment;
    }

    public function test_only_admins_hold_the_payment_permissions(): void
    {
        $this->assertTrue($this->userWithRole('admin')->can('view payments'));
        $this->assertTrue($this->userWithRole('admin')->can('manage payments'));
        $this->assertFalse($this->userWithRole('school_admin')->can('view payments'));
        $this->assertFalse($this->userWithRole('content_creator')->can('view payments'));
        $this->assertFalse($this->userWithRole('parent')->can('view payments'));
    }

    public function test_admin_can_list_payments(): void
    {
        $payment = $this->payment('LQ-ADMIN-0001');

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$payment]);
    }

    public function test_panel_users_without_the_permission_are_forbidden(): void
    {
        $this->payment('LQ-ADMIN-0002');

        foreach (['content_creator', 'school_admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get('/admin/payments')
                ->assertForbidden();
        }
    }

    public function test_parents_cannot_reach_the_admin_payments(): void
    {
        $this->actingAs($this->userWithRole('parent'))
            ->get('/admin/payments')
            ->assertForbidden();
    }

    public function test_payments_can_be_searched_by_parent_email_and_reference(): void
    {
        $parent = $this->userWithRole('parent');
        $mine = $this->payment('LQ-SEARCH-0001', Payment::STATUS_SUCCEEDED, ['parent' => $parent]);
        $other = $this->payment('LQ-SEARCH-0002');

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->searchTable($parent->email)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$other])
            ->searchTable('LQ-SEARCH-0002')
            ->assertCanSeeTableRecords([$other])
            ->assertCanNotSeeTableRecords([$mine]);
    }

    public function test_late_settlements_can_be_filtered(): void
    {
        $late = $this->payment('LQ-LATE-0001', Payment::STATUS_SUCCEEDED, [
            'metadata' => ['late_settlement' => true],
        ]);
        $normal = $this->payment('LQ-LATE-0002');

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->filterTable('late_settlement', true)
            ->assertCanSeeTableRecords([$late])
            ->assertCanNotSeeTableRecords([$normal]);
    }

    public function test_stuck_filter_shows_only_old_unsettled_payments(): void
    {
        $stuck = $this->payment('LQ-STUCK-0001', Payment::STATUS_PENDING, ['created_at' => now()->subHours(3)]);
        $fresh = $this->payment('LQ-STUCK-0002', Payment::STATUS_PENDING);

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->filterTable('stuck', true)
            ->assertCanSeeTableRecords([$stuck])
            ->assertCanNotSeeTableRecords([$fresh]);
    }

    public function test_admin_can_check_a_pending_payment_with_the_provider(): void
    {
        $payment = $this->payment('LQ-RECON-ADM-1', Payment::STATUS_PENDING);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'id' => 987654,
                    'reference' => $payment->reference,
                    'amount' => $payment->amount_minor,
                    'currency' => 'KES',
                    'status' => 'success',
                ],
            ]),
        ]);

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->callTableAction('reconcile', $payment)
            ->assertNotified('Payment confirmed and settled');

        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->fresh()->status);
    }

    public function test_check_action_is_hidden_for_settled_payments(): void
    {
        $payment = $this->payment('LQ-RECON-ADM-2', Payment::STATUS_SUCCEEDED);

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListPayments::class)
            ->assertTableActionHidden('reconcile', $payment);
    }

    public function test_viewers_without_manage_permission_cannot_check_payments(): void
    {
        $payment = $this->payment('LQ-RECON-ADM-3', Payment::STATUS_PENDING);

        $viewer = $this->userWithRole('school_admin');
        $viewer->givePermissionTo('view payments');

        $this->actingAs($viewer);

        Livewire::test(ListPayments::class)
            ->assertCanSeeTableRecords([$payment])
            ->assertTableActionHidden('reconcile', $payment);
    }

    public function test_payment_detail_page_renders_without_provider_payloads(): void
    {
        $payment = $this->payment('LQ-VIEW-0001', Payment::STATUS_SUCCEEDED, [
            'metadata' => ['late_settlement' => true],
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/admin/payments/' . $payment->id)
            ->assertOk()
            ->assertSee('LQ-VIEW-0001')
            ->assertSee('Paid after the checkout window closed');
    }

    public function test_provider_events_show_on_the_payment(): void
    {
        $payment = $this->payment('LQ-EVENTS-0001');

        $event = WebhookEvent::create([
            'payment_id' => $payment->id,
            'provider' => 'paystack',
            'event_id' => 'LQ-EVENTS-0001:SUCCESS',
            'event_type' => 'status_check',
            'payment_reference' => $payment->reference,
            'status' => WebhookEvent::STATUS_PROCESSED,
            'signature_verified_at' => now(),
            'payload' => ['provider_status' => 'SUCCESS'],
            'attempts' => 1,
            'processed_at' => now(),
        ]);

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(WebhookEventsRelationManager::class, [
            'ownerRecord' => $payment,
            'pageClass' => ViewPayment::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$event]);
    }
}
