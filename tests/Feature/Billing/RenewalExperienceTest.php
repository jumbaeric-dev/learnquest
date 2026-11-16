<?php

namespace Tests\Feature\Billing;

use App\Livewire\ParentPortal\Billing\Index as BillingIndex;
use App\Livewire\ParentPortal\Dashboard\Index as DashboardIndex;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RenewalExperienceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.billing.enforced' => true,
            'learnquest.gateways.default_provider' => 'intasend',
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.driver' =>
                \App\Services\Billing\Gateways\IntaSendGateway::class,
            'learnquest.gateways.providers.intasend.currencies' => ['KES'],
            'learnquest.gateways.providers.intasend.public_key' => 'test-public-key',
            'learnquest.gateways.providers.intasend.secret_key' => 'test-secret-key',
        ]);
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create();
        $parent->assignRole('parent');

        return $parent;
    }

    private function subscribe(User $parent, string $status, $endsAt): Subscription
    {
        return Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => $status,
            'starts_at' => now()->subDays(20),
            'ends_at' => $endsAt,
            'auto_renews' => false,
        ]);
    }

    public function test_a_trialing_parent_can_choose_a_plan(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->addDays(10));

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('Choose Monthly');
    }

    public function test_an_active_parent_can_add_time(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->addDays(20));

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertSee('Add Monthly')
            ->assertSee('stacked onto your current end date');
    }

    public function test_dashboard_warns_when_a_trial_is_about_to_end(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->addDays(2));

        Livewire::actingAs($parent)
            ->test(DashboardIndex::class)
            ->assertSee('Your free trial ends in 2 days');
    }

    public function test_dashboard_shows_nothing_when_there_is_plenty_of_time(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->addDays(40));

        Livewire::actingAs($parent)
            ->test(DashboardIndex::class)
            ->assertDontSee('ends in')
            ->assertDontSee('ended on');
    }

    public function test_dashboard_shows_a_recently_ended_plan(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'active', now()->subDays(2));

        Livewire::actingAs($parent)
            ->test(DashboardIndex::class)
            ->assertSee('ended on')
            ->assertSee('Choose a plan to unlock everything again');
    }

    public function test_the_banner_is_not_repeated_on_the_billing_page(): void
    {
        $parent = $this->parentAccount();
        $this->subscribe($parent, 'trialing', now()->addDays(2));

        Livewire::actingAs($parent)
            ->test(BillingIndex::class)
            ->assertDontSee('Choose a plan to keep everything unlocked');
    }
}