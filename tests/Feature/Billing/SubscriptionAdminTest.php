<?php

namespace Tests\Feature\Billing;

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Resources\Subscriptions\RelationManagers\GrantsRelationManager;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionGrant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SubscriptionAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PlanSeeder::class);

        config(['learnquest.billing.enforced' => true]);
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    private function subscription(?User $parent = null, string $status = 'active', $endsAt = null): Subscription
    {
        $parent ??= $this->userWithRole('parent');

        return Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => $status,
            'starts_at' => now()->subDays(5),
            'ends_at' => $endsAt ?? now()->addDays(20),
            'auto_renews' => false,
            'gateway' => 'paystack',
        ]);
    }

    public function test_admin_can_list_subscriptions(): void
    {
        $subscription = $this->subscription();

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListSubscriptions::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$subscription]);
    }

    public function test_panel_users_without_the_permission_and_parents_are_forbidden(): void
    {
        foreach (['content_creator', 'school_admin', 'parent'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get('/admin/subscriptions')
                ->assertForbidden();
        }
    }

    public function test_filters_find_subscriptions_ending_soon(): void
    {
        $soon = $this->subscription(null, 'active', now()->addDays(3));
        $later = $this->subscription(null, 'active', now()->addDays(40));

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListSubscriptions::class)
            ->filterTable('ending_soon', true)
            ->assertCanSeeTableRecords([$soon])
            ->assertCanNotSeeTableRecords([$later]);
    }

    public function test_admin_can_grant_complimentary_time_from_the_list(): void
    {
        $admin = $this->userWithRole('admin');
        $parent = $this->userWithRole('parent');
        $premium = Plan::where('key', 'premium')->firstOrFail();

        $this->actingAs($admin);

        Livewire::test(ListSubscriptions::class)
            ->callAction('grant', data: [
                'user_id' => $parent->id,
                'plan_id' => $premium->id,
                'days' => 30,
                'reason' => 'Late M-Pesa confirmation, ref LQ-123',
            ])
            ->assertHasNoActionErrors()
            ->assertNotified('Complimentary time granted');

        $grant = SubscriptionGrant::query()->where('user_id', $parent->id)->sole();

        $this->assertSame($admin->id, $grant->granted_by);
        $this->assertSame(30, $grant->days);
    }

    public function test_the_grant_form_validates_its_input(): void
    {
        $parent = $this->userWithRole('parent');
        $premium = Plan::where('key', 'premium')->firstOrFail();

        $this->actingAs($this->userWithRole('admin'));

        Livewire::test(ListSubscriptions::class)
            ->callAction('grant', data: [
                'user_id' => $parent->id,
                'plan_id' => $premium->id,
                'days' => 0,
                'reason' => 'short',
            ])
            ->assertHasActionErrors(['days', 'reason']);

        $this->assertSame(0, SubscriptionGrant::count());
    }

    public function test_viewers_without_manage_permission_cannot_grant(): void
    {
        $viewer = $this->userWithRole('school_admin');
        $viewer->givePermissionTo('view payments');

        $this->actingAs($viewer);

        Livewire::test(ListSubscriptions::class)
            ->assertSuccessful()
            ->assertActionHidden('grant');
    }

    public function test_the_grant_audit_trail_shows_on_the_subscription(): void
    {
        $admin = $this->userWithRole('admin');
        $subscription = $this->subscription();

        $grant = SubscriptionGrant::create([
            'subscription_id' => $subscription->id,
            'user_id' => $subscription->user_id,
            'granted_by' => $admin->id,
            'plan_id' => $subscription->plan_id,
            'days' => 14,
            'previous_ends_at' => $subscription->ends_at,
            'new_ends_at' => $subscription->ends_at->copy()->addDays(14),
            'reason' => 'Goodwill after a failed payment',
        ]);

        $this->actingAs($admin);

        Livewire::test(GrantsRelationManager::class, [
            'ownerRecord' => $subscription,
            'pageClass' => ViewSubscription::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$grant]);

        $this->get('/admin/subscriptions/' . $subscription->id)->assertOk();
    }
}