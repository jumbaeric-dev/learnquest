<?php

namespace Tests\Feature\Mail;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\PlanPrice;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ComplimentaryTimeGrantedNotification;
use App\Notifications\ParentResetPasswordNotification;
use App\Notifications\PaymentReceiptNotification;
use App\Notifications\WelcomeParentNotification;
use App\Services\Billing\PaymentReconciliationService;
use App\Services\Billing\SubscriptionService;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class TransactionalEmailTest extends TestCase
{
    use RefreshDatabase;

    private const STATUS_URL = 'https://api.intasend.com/api/v1/payment/status/';
    private const GENERIC_STATUS = 'If that email belongs to a parent account, we’ve sent a link to reset the password.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->seed(RolesSeeder::class);
        $this->seed(RolePermissionSeeder::class);
        $this->seed(PlanSeeder::class);

        config([
            'learnquest.support_email' => 'support@learnquest.test',
            'learnquest.gateways.providers.intasend.enabled' => true,
            'learnquest.gateways.providers.intasend.secret_key' => 'ISSecretKey_test_example',
            'learnquest.gateways.providers.intasend.public_key' => 'ISPubKey_test_example',
            'learnquest.gateways.providers.intasend.webhook_secret' => 'test-challenge',
            'learnquest.gateways.providers.intasend.base_url' => 'https://api.intasend.com',
        ]);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        return $user;
    }

    private function monthlyPrice(): PlanPrice
    {
        return PlanPrice::query()
            ->where('currency', 'KES')
            ->where('months', 1)
            ->whereHas('plan', fn ($query) => $query->where('key', 'premium'))
            ->firstOrFail();
    }

    private function payment(User $parent, string $reference, string $status, array $overrides = []): Payment
    {
        $price = $this->monthlyPrice();

        return Payment::create(array_merge([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'plan_price_id' => $price->id,
            'plan_key' => 'premium',
            'duration_months' => 1,
            'reference' => $reference,
            'provider' => 'intasend',
            'amount_minor' => $price->amount_minor,
            'currency' => 'KES',
            'status' => $status,
            'expires_at' => now()->addMinutes(30),
            'paid_at' => $status === Payment::STATUS_SUCCEEDED ? now() : null,
        ], $overrides));
    }

    // ---- Branding -----------------------------------------------------------------

    public function test_the_branded_theme_is_configured_and_renders(): void
    {
        $this->assertSame('learnquest', config('mail.markdown.theme'));
        $this->assertFileExists(resource_path('views/vendor/mail/html/themes/learnquest.css'));

        $html = (string) (new WelcomeParentNotification())
            ->toMail($this->userWithRole('parent'))
            ->render();

        $this->assertStringContainsString('#0891b2', $html);
        $this->assertStringContainsString('Add your first child', $html);
    }

    // ---- Receipts -----------------------------------------------------------------

    public function test_a_receipt_is_sent_once_when_a_payment_settles(): void
    {
        Notification::fake();

        $parent = $this->userWithRole('parent');
        $payment = $this->payment($parent, 'LQ-MAIL-0001', Payment::STATUS_PENDING, [
            'metadata' => ['intasend_invoice_id' => 'INV-1'],
        ]);

        Http::fake([self::STATUS_URL => Http::response(['invoice' => [
            'invoice_id' => 'INV-1', 'api_ref' => $payment->reference, 'state' => 'COMPLETE',
            'value' => number_format($payment->amount_minor / 100, 2, '.', ''), 'currency' => 'KES', 'provider_ref' => 'MPESA-1',
        ]])]);

        $service = app(PaymentReconciliationService::class);
        $service->reconcile($payment);
        $service->reconcile($payment->fresh());

        Notification::assertSentToTimes($parent, PaymentReceiptNotification::class, 1);
    }

    public function test_the_receipt_email_shows_the_payment_details(): void
    {
        $parent = $this->userWithRole('parent');
        $price = $this->monthlyPrice();

        $subscription = Subscription::create([
            'user_id' => $parent->id,
            'plan_id' => $price->plan_id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
            'auto_renews' => false,
            'gateway' => 'intasend',
        ]);

        $payment = $this->payment($parent, 'LQ-RECEIPT-0001', Payment::STATUS_SUCCEEDED, [
            'subscription_id' => $subscription->id,
        ]);

        $mail = (new PaymentReceiptNotification($payment))->toMail($parent);
        $html = (string) $mail->render();

        $this->assertStringContainsString('LQ-RECEIPT-0001', $html);
        $this->assertStringContainsString($payment->formattedAmount(), $html);
        $this->assertStringContainsString($price->plan->name, $html);
        $this->assertStringContainsString('Access until', $html);
        $this->assertSame('support@learnquest.test', $mail->replyTo[0][0]);
    }

    // ---- Welcome ------------------------------------------------------------------

    public function test_registration_sends_a_welcome_email_with_the_trial_when_billing_is_live(): void
    {
        Notification::fake();
        config(['learnquest.billing.enforced' => true]);

        $this->post(route('parent.register.submit'), [
            'name' => 'Wanjiru Kamau',
            'email' => 'wanjiru@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ])->assertRedirect(route('parent.dashboard'));

        $user = User::where('email', 'wanjiru@example.test')->firstOrFail();

        Notification::assertSentTo(
            $user,
            WelcomeParentNotification::class,
            fn ($notification) => $notification->trialEndsAt !== null
        );
    }

    public function test_the_welcome_email_has_no_trial_line_before_billing_launches(): void
    {
        Notification::fake();
        config(['learnquest.billing.enforced' => false]);

        $this->post(route('parent.register.submit'), [
            'name' => 'Wanjiru Kamau',
            'email' => 'wanjiru@example.test',
            'password' => 'secret-pass-123',
            'password_confirmation' => 'secret-pass-123',
        ]);

        $user = User::where('email', 'wanjiru@example.test')->firstOrFail();

        Notification::assertSentTo(
            $user,
            WelcomeParentNotification::class,
            fn ($notification) => $notification->trialEndsAt === null
        );
    }

    // ---- Complimentary time -------------------------------------------------------

    public function test_a_grant_emails_the_parent_without_revealing_the_internal_reason(): void
    {
        Notification::fake();

        $parent = $this->userWithRole('parent');
        $reason = 'Late M-Pesa confirmation, customer emailed support';

        $grant = app(SubscriptionService::class)->grantComplimentary(
            $parent,
            Plan::where('key', 'premium')->firstOrFail(),
            30,
            $reason,
            $this->userWithRole('admin'),
        );

        Notification::assertSentTo($parent, ComplimentaryTimeGrantedNotification::class);

        $html = (string) (new ComplimentaryTimeGrantedNotification($grant))->toMail($parent)->render();

        $this->assertStringContainsString('30 days', $html);
        $this->assertStringNotContainsString($reason, $html);
    }

    // ---- Password reset -----------------------------------------------------------

    public function test_the_login_page_links_to_password_reset(): void
    {
        $this->get(route('parent.login'))->assertOk()->assertSee('Forgot your password?');
        $this->get(route('parent.password.request'))->assertOk();
    }

    public function test_a_parent_gets_a_reset_email_and_unknown_or_non_parent_emails_get_nothing(): void
    {
        Notification::fake();

        $parent = $this->userWithRole('parent', ['email' => 'mum@example.test']);
        $teacher = $this->userWithRole('teacher', ['email' => 'teacher@example.test']);

        foreach (['mum@example.test', 'nobody@example.test', 'teacher@example.test'] as $email) {
            $this->post(route('parent.password.email'), ['email' => $email])
                ->assertSessionHas('status', self::GENERIC_STATUS);
        }

        Notification::assertSentTo($parent, ParentResetPasswordNotification::class);
        Notification::assertNotSentTo($teacher, ParentResetPasswordNotification::class);
        Notification::assertCount(1);
    }

    public function test_a_parent_can_reset_their_password_with_the_emailed_token(): void
    {
        Notification::fake();

        $parent = $this->userWithRole('parent', ['email' => 'mum@example.test']);

        $this->post(route('parent.password.email'), ['email' => $parent->email]);

        $token = null;
        Notification::assertSentTo($parent, ParentResetPasswordNotification::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $this->post(route('parent.password.update'), [
            'token' => $token,
            'email' => $parent->email,
            'password' => 'NewPass-12345',
            'password_confirmation' => 'NewPass-12345',
        ])->assertRedirect(route('parent.login'))
            ->assertSessionHas('status', 'Your password has been reset. You can sign in now.');

        $this->assertTrue(Hash::check('NewPass-12345', $parent->fresh()->password));

        // The token is single-use.
        $this->post(route('parent.password.update'), [
            'token' => $token,
            'email' => $parent->email,
            'password' => 'Another-12345',
            'password_confirmation' => 'Another-12345',
        ])->assertSessionHasErrors('email');
    }

    public function test_an_invalid_reset_token_is_rejected(): void
    {
        $parent = $this->userWithRole('parent', ['email' => 'mum@example.test']);

        $this->post(route('parent.password.update'), [
            'token' => 'not-a-real-token',
            'email' => $parent->email,
            'password' => 'NewPass-12345',
            'password_confirmation' => 'NewPass-12345',
        ])->assertSessionHasErrors('email');

        $this->assertFalse(Hash::check('NewPass-12345', $parent->fresh()->password));
    }

    public function test_a_non_parent_cannot_reset_a_password_through_the_parent_form(): void
    {
        $teacher = $this->userWithRole('teacher', ['email' => 'teacher@example.test']);
        $token = Password::createToken($teacher);

        $this->post(route('parent.password.update'), [
            'token' => $token,
            'email' => $teacher->email,
            'password' => 'NewPass-12345',
            'password_confirmation' => 'NewPass-12345',
        ])->assertForbidden();
    }

    public function test_the_reset_email_links_to_the_parent_reset_page(): void
    {
        $parent = $this->userWithRole('parent', ['email' => 'mum@example.test']);

        $html = (string) (new ParentResetPasswordNotification('tok123'))->toMail($parent)->render();

        $this->assertStringContainsString('/parent/reset-password/tok123', $html);
        $this->assertStringContainsString('mum%40example.test', $html);
    }
}