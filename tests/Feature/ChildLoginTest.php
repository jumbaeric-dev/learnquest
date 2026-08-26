<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChildLoginTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    /*
        |--------------------------------------------------------------------------
        | Child Role
        |--------------------------------------------------------------------------
        |
        | The child login tests create a child user and assign the `child`
        | role. Create it here so the tests do not depend on the application's
        | production database seeders.
        |
        */

    Role::findOrCreate("child", "web");
  }

  /*
    |--------------------------------------------------------------------------
    | Login Page
    |--------------------------------------------------------------------------
    */

  public function test_child_login_page_is_available(): void
  {
    $response = $this->get(route("login"));

    $response->assertOk();
    $response->assertViewIs("auth.child-login");
  }

  /*
    |--------------------------------------------------------------------------
    | Successful Login
    |--------------------------------------------------------------------------
    */

  public function test_child_can_login_with_username_and_pin(): void
  {
    $user = User::factory()->create();

    $user->assignRole("child");

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "parent_id" => $user->id,
      "username" => "StarCaptain99",
      "pin" => Hash::make("1234"),
      "is_active" => true,
    ]);

    $response = $this->post(route("child.login"), [
      "username" => "StarCaptain99",
      "pin" => "1234",
    ]);

    $response->assertRedirect(route("child.dashboard"));

    $this->assertAuthenticatedAs($user);

    $this->assertEquals($child->id, session("active_child_id"));
  }

  /*
    |--------------------------------------------------------------------------
    | Invalid PIN
    |--------------------------------------------------------------------------
    */

  public function test_child_cannot_login_with_wrong_pin(): void
  {
    $user = User::factory()->create();

    $user->assignRole("child");

    Child::factory()->create([
      "user_id" => $user->id,
      "parent_id" => $user->id,
      "username" => "StarCaptain99",
      "pin" => Hash::make("1234"),
      "is_active" => true,
    ]);

    $response = $this->post(route("child.login"), [
      "username" => "StarCaptain99",
      "pin" => "9999",
    ]);

    $response->assertSessionHasErrors("username");

    $this->assertGuest();

    $this->assertNull(session("active_child_id"));
  }

  /*
    |--------------------------------------------------------------------------
    | Inactive Child
    |--------------------------------------------------------------------------
    */

  public function test_inactive_child_cannot_login(): void
  {
    $user = User::factory()->create();

    $user->assignRole("child");

    Child::factory()->create([
      "user_id" => $user->id,
      "parent_id" => $user->id,
      "username" => "InactiveExplorer",
      "pin" => Hash::make("1234"),
      "is_active" => false,
    ]);

    $response = $this->post(route("child.login"), [
      "username" => "InactiveExplorer",
      "pin" => "1234",
    ]);

    $response->assertSessionHasErrors("username");

    $this->assertGuest();

    $this->assertNull(session("active_child_id"));
  }

  /*
    |--------------------------------------------------------------------------
    | Unknown Username
    |--------------------------------------------------------------------------
    */

  public function test_unknown_username_cannot_login(): void
  {
    $response = $this->post(route("child.login"), [
      "username" => "DoesNotExist",
      "pin" => "1234",
    ]);

    $response->assertSessionHasErrors("username");

    $this->assertGuest();

    $this->assertNull(session("active_child_id"));
  }

  /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

  public function test_authenticated_child_can_logout(): void
  {
    $user = User::factory()->create();

    $user->assignRole("child");

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "parent_id" => $user->id,
      "username" => "LogoutExplorer",
      "pin" => Hash::make("1234"),
      "is_active" => true,
    ]);

    $this->actingAs($user);

    session([
      "active_child_id" => $child->id,
    ]);

    $response = $this->post(route("logout"));

    $response->assertRedirect(route("login"));

    $this->assertGuest();

    $this->assertNull(session("active_child_id"));
  }

  /*
    |--------------------------------------------------------------------------
    | Guest Cannot Logout
    |--------------------------------------------------------------------------
    */

  public function test_guest_cannot_logout(): void
  {
    $response = $this->post(route("logout"));

    $response->assertRedirect(route("login"));

    $this->assertGuest();
  }
}
