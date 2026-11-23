<?php

namespace Tests\Feature\Mail;

use App\Livewire\ParentPortal\Account\Settings;
use App\Livewire\ParentPortal\Dashboard\Index as DashboardIndex;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ParentVerifyEmailNotification;
use App\Services\Billing\BillingCheckoutService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(RolesSeeder::class);
        $this->seed(PlanSeeder::class);
    }

    private function parentAccount(bool $verified = false): User
    {
        $user = $verified
            ? User::factory()->create()
            : User::factory()->unverified()->create();

        $user->assignRole('parent');

        return $user;
    }

    private function verifyUrl(User $user, int $minutes = 60, ?string $hash = null): string
    {
        return URL::temporarySignedRoute(
            'parent.verification.verify',
            now()->addMinutes($minutes),
            ['id' => $user->id, 'hash' => $hash ?? sha1($user->email)],
        );
    }

    private function register(): void
    {
        $this->post(route('parent.register.submit'), [
            'name' => 'Wanjiru Kamau',
            'email' => 'wanjiru@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect(route('parent.dashboard'));
    }

    public function test_registration_sends_a_verification_email_and_leaves_the_address_unverified(): void
    {
        Notification::fake();

        $this->register();

        $user = User::where('email', 'wanjiru@example.test')->firstOrFail();

        Notification::assertSentTo($user, ParentVerifyEmailNotification::class);
        $this->assertNull($user->email_verified_at);
    }

    public function test_the_emailed_link_verifies_without_needing_a_login(): void
    {
        $user = $this->parentAccount();

        $this->get($this->verifyUrl($user))
            ->assertRedirect(route('parent.login'))
            ->assertSessionHas('status');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_a_signed_in_parent_lands_on_the_dashboard_after_verifying(): void
    {
        $user = $this->parentAccount();

        $this->actingAs($user)
            ->get($this->verifyUrl($user))
            ->assertRedirect(route('parent.dashboard'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_tampered_expired_and_unsigned_links_are_rejected(): void
    {
        $user = $this->parentAccount();

        $this->get($this->verifyUrl($user, 60, sha1('someone-else@example.test')))->assertForbidden();
        $this->get($this->verifyUrl($user, -5))->assertForbidden();
        $this->get(route('parent.verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_only_parent_accounts_can_be_verified_here(): void
    {
        $teacher = User::factory()->unverified()->create();
        $teacher->assignRole('teacher');

        $this->get($this->verifyUrl($teacher))->assertForbidden();
    }

    public function test_an_old_link_stops_working_after_the_email_changes(): void
    {
        $user = $this->parentAccount();
        $oldLink = $this->verifyUrl($user);

        $user->forceFill(['email' => 'new-address@example.test'])->save();

        $this->get($oldLink)->assertForbidden();
    }

    public function test_a_parent_can_ask_for_a_new_link(): void
    {
        Notification::fake();

        $user = $this->parentAccount();

        $this->actingAs($user)
            ->post(route('parent.verification.send'))
            ->assertSessionHas('verification_status');

        Notification::assertSentTo($user, ParentVerifyEmailNotification::class);
    }

    public function test_a_verified_parent_is_not_emailed_again(): void
    {
        Notification::fake();

        $user = $this->parentAccount(true);

        $this->actingAs($user)->post(route('parent.verification.send'));

        Notification::assertNothingSent();
    }

    public function test_the_banner_shows_for_unverified_parents_only(): void
    {
        Livewire::actingAs($this->parentAccount(false))
            ->test(DashboardIndex::class)
            ->assertSee('Please verify your email address');

        Livewire::actingAs($this->parentAccount(true))
            ->test(DashboardIndex::class)
            ->assertDontSee('Please verify your email address');
    }

    public function test_checkout_is_blocked_until_the_email_is_verified(): void
    {
        $parent = $this->parentAccount(false);

        try {
            app(BillingCheckoutService::class)->startCheckout($parent, 1, (string) Str::uuid());
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('checkout', $exception->errors());
            $this->assertStringContainsString('verify your email', $exception->errors()['checkout'][0]);
        }
    }

    public function test_changing_the_account_email_requires_verifying_the_new_address(): void
    {
        Notification::fake();

        $parent = $this->parentAccount(true);

        Livewire::actingAs($parent)
            ->test(Settings::class)
            ->set('name', $parent->name)
            ->set('email', 'brand-new@example.test')
            ->call('saveProfile')
            ->assertHasNoErrors();

        $parent->refresh();

        $this->assertSame('brand-new@example.test', $parent->email);
        $this->assertNull($parent->email_verified_at);
        Notification::assertSentTo($parent, ParentVerifyEmailNotification::class);
    }

    public function test_the_trial_waits_for_verification_when_the_switch_is_on(): void
    {
        config([
            'learnquest.billing.enforced' => true,
            'learnquest.billing.trial.requires_verified_email' => true,
        ]);

        $this->register();

        $user = User::where('email', 'wanjiru@example.test')->firstOrFail();
        $this->assertSame(0, Subscription::where('user_id', $user->id)->count());

        $this->get($this->verifyUrl($user))->assertRedirect();

        $this->assertTrue(
            Subscription::where('user_id', $user->id)->where('status', 'trialing')->exists()
        );
    }

    public function test_the_verification_email_carries_a_signed_link(): void
    {
        $user = $this->parentAccount();

        $html = (string) (new ParentVerifyEmailNotification())->toMail($user)->render();

        $this->assertStringContainsString('/parent/verify-email/' . $user->id, $html);
        $this->assertStringContainsString('signature=', $html);
        $this->assertStringContainsString('Verify email address', $html);
    }
}