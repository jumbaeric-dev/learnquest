<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        Role::findOrCreate("parent", "web");
        Role::findOrCreate("child", "web");
    }

    protected function makeParent(string $email = "mum@example.test"): User
    {
        $user = User::factory()->create([
            "email" => $email,
            "password" => "secret-pass-123",
        ]);

        $user->assignRole("parent");

        return $user;
    }

    public function test_login_and_register_pages_are_available(): void
    {
        $this->get(route("parent.login"))->assertOk();
        $this->get(route("parent.register"))->assertOk();
    }

    public function test_parent_can_sign_in_and_reach_the_dashboard(): void
    {
        $parent = $this->makeParent();

        $this->post(route("parent.login.submit"), [
            "email" => "MUM@example.test",
            "password" => "secret-pass-123",
        ])->assertRedirect(route("parent.dashboard"));

        $this->assertAuthenticatedAs($parent);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->makeParent();

        $this->from(route("parent.login"))
            ->post(route("parent.login.submit"), [
                "email" => "mum@example.test",
                "password" => "not-the-password",
            ])
            ->assertSessionHasErrors("email");

        $this->assertGuest();
    }

    public function test_non_parent_accounts_cannot_use_the_parent_login(): void
    {
        $childUser = User::factory()->create([
            "email" => "kid@learnquest.test",
            "password" => "secret-pass-123",
        ]);
        $childUser->assignRole("child");

        $this->post(route("parent.login.submit"), [
            "email" => "kid@learnquest.test",
            "password" => "secret-pass-123",
        ])->assertSessionHasErrors("email");

        $this->assertGuest();
    }

    public function test_guests_are_sent_to_the_parent_login(): void
    {
        $this->get(route("parent.dashboard"))->assertRedirect(
            route("parent.login")
        );
    }

    public function test_signed_in_non_parents_are_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route("parent.dashboard"))
            ->assertForbidden();
    }

    public function test_registration_creates_a_parent_and_signs_them_in(): void
    {
        $this->post(route("parent.register.submit"), [
            "name" => "Wanjiru Kamau",
            "email" => "Wanjiru@Example.test",
            "password" => "secret-pass-123",
            "password_confirmation" => "secret-pass-123",
        ])->assertRedirect(route("parent.dashboard"));

        $user = User::where("email", "wanjiru@example.test")->firstOrFail();

        $this->assertTrue($user->isParent());
        $this->assertAuthenticatedAs($user);
    }

    public function test_registration_rejects_a_duplicate_email(): void
    {
        $this->makeParent("taken@example.test");

        $this->post(route("parent.register.submit"), [
            "name" => "Someone Else",
            "email" => "taken@example.test",
            "password" => "secret-pass-123",
            "password_confirmation" => "secret-pass-123",
        ])->assertSessionHasErrors("email");
    }

    public function test_logout_sends_a_parent_back_to_the_parent_login(): void
    {
        $parent = $this->makeParent();

        $this->actingAs($parent)
            ->post(route("logout"))
            ->assertRedirect(route("parent.login"));
    }
}
