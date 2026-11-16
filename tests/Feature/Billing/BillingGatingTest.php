<?php

namespace Tests\Feature\Billing;

use App\Exceptions\ContentLockedException;
use App\Livewire\ParentPortal\Children\Index;
use App\Models\Child;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BillingGatingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate('parent', 'web');
        Role::findOrCreate('child', 'web');
        $this->seed(PlanSeeder::class);

        config(['learnquest.billing.enforced' => true]);
    }

    private function parentAccount(): User
    {
        $parent = User::factory()->create(['password' => 'secret-pass-123']);
        $parent->assignRole('parent');

        return $parent;
    }

    private function makePremium(User $parent): void
    {
        Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => Plan::where('key', 'premium')->value('id'),
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ]);
    }

    public function test_free_parent_cannot_add_a_second_child(): void
    {
        $parent = $this->parentAccount();
        Child::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->assertSee('includes 1 child')
            ->set('first_name', 'Zed')
            ->call('createChild')
            ->assertHasErrors(['first_name']);

        $this->assertSame(1, $parent->children()->count());
    }

    public function test_premium_parent_can_add_children_up_to_the_limit(): void
    {
        $parent = $this->parentAccount();
        $this->makePremium($parent);
        Child::factory()->create(['parent_id' => $parent->id]);

        Livewire::actingAs($parent)
            ->test(Index::class)
            ->set('first_name', 'Zed')
            ->call('createChild')
            ->assertHasNoErrors();

        $this->assertSame(2, $parent->children()->count());
    }

    public function test_billing_page_shows_plan_and_is_parent_only(): void
    {
        $parent = $this->parentAccount();

        $this->actingAs($parent)
            ->get(route('parent.billing'))
            ->assertOk()
            ->assertSee('Explorer')
            ->assertSee('Family Premium')
            ->assertSee('KSh 499');

        // Authenticate the second actor in a fresh session so auth.session does not
        // invalidate the previous parent's session hash and redirect before the role check.
        $this->flushSession();

        $this->actingAs(User::factory()->create())
            ->get(route('parent.billing'))
            ->assertForbidden();
    }

    public function test_billing_page_can_switch_currency(): void
    {
        $parent = $this->parentAccount();

        Livewire::actingAs($parent)
            ->test(\App\Livewire\ParentPortal\Billing\Index::class)
            ->call('setCurrency', 'USD')
            ->assertSee('$4.99');
    }

    public function test_guests_are_sent_to_the_parent_login_from_billing(): void
    {
        $this->get(route('parent.billing'))->assertRedirect(route('parent.login'));
    }

    public function test_registration_starts_a_trial_when_billing_is_enforced(): void
    {
        $this->post(route('parent.register.submit'), [
            'name' => 'Wanjiru Kamau',
            'email' => 'wanjiru@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect(route('parent.dashboard'));

        $user = User::where('email', 'wanjiru@example.test')->firstOrFail();

        $this->assertTrue(
            Subscription::where('user_id', $user->id)->where('status', 'trialing')->exists()
        );
    }

    public function test_registration_when_free_is_disabled_does_not_grant_grandfathered_free_access(): void
    {
        config(['learnquest.billing.free_plan_enabled' => false]);

        $this->post(route('parent.register.submit'), [
            'name' => 'New Parent',
            'email' => 'new-parent@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect(route('parent.dashboard'));

        $user = User::where('email', 'new-parent@example.test')->firstOrFail();
        $this->assertFalse($user->free_plan_grandfathered);
        $this->assertTrue(Subscription::where('user_id', $user->id)->where('status', 'trialing')->exists());
    }

    public function test_registration_starts_no_trial_before_launch(): void
    {
        config(['learnquest.billing.enforced' => false]);

        $this->post(route('parent.register.submit'), [
            'name' => 'Wanjiru Kamau',
            'email' => 'wanjiru@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ]);

        $this->assertSame(0, Subscription::count());
    }

    public function test_start_trials_command_covers_existing_parents_once(): void
    {
        $this->parentAccount();
        $this->parentAccount();

        $this->artisan('learnquest:start-trials')
            ->expectsOutputToContain('Started 2 trials')
            ->assertSuccessful();

        $this->artisan('learnquest:start-trials')
            ->expectsOutputToContain('Started 0 trials')
            ->assertSuccessful();
    }

    public function test_start_trials_command_refuses_to_run_before_launch(): void
    {
        config(['learnquest.billing.enforced' => false]);

        $this->artisan('learnquest:start-trials')->assertFailed();
    }

    public function test_free_parent_sees_an_upgrade_prompt_instead_of_skill_reports(): void
    {
        $parent = $this->parentAccount();
        $child = Child::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($parent)
            ->get(route('parent.children.show', $child))
            ->assertOk()
            ->assertSee('Skill reports are part of Premium')
            ->assertDontSee('Growth opportunities');
    }

    public function test_premium_parent_sees_skill_reports(): void
    {
        $parent = $this->parentAccount();
        $this->makePremium($parent);
        $child = Child::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($parent)
            ->get(route('parent.children.show', $child))
            ->assertOk()
            ->assertSee('Growth opportunities')
            ->assertDontSee('Skill reports are part of Premium');
    }

    public function test_locked_content_renders_the_friendly_page(): void
    {
        Route::get('/_test/locked', function () {
            throw new ContentLockedException('locked');
        });

        $this->get('/_test/locked')
            ->assertForbidden()
            ->assertSee('This adventure is part of Premium');
    }
}