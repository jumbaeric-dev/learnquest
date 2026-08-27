<?php

namespace Tests\Feature\Filament\Children;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CreateChildTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    /*
     * Filament panel access.
     */
    $adminRole = Role::findOrCreate("admin", "web");

    /*
     * Content creators can access the Filament panel,
     * but cannot manage child accounts.
     */
    Role::findOrCreate("content_creator", "web");

    /*
     * ChildAccountService assigns this role to the
     * User created for every child account.
     */
    Role::findOrCreate("child", "web");

    /*
     * ChildPolicy::create() requires "manage users".
     */
    $manageUsers = Permission::findOrCreate("manage users", "web");

    $adminRole->givePermissionTo($manageUsers);
  }

  private function createAdmin(): User
  {
    $user = User::factory()->create();

    $user->assignRole("admin");

    return $user;
  }

  public function test_authorized_user_can_open_create_child_page(): void
  {
    $admin = $this->createAdmin();

    $this->actingAs($admin);

    $response = $this->get("/admin/children/create");

    $response->assertSuccessful();
  }

  public function test_unauthorized_user_cannot_open_create_child_page(): void
  {
    $user = User::factory()->create();

    /*
     * Content creators can access the Filament panel,
     * but do not have permission to manage users/children.
     */
    $user->assignRole("content_creator");

    $this->actingAs($user);

    $response = $this->get("/admin/children/create");

    $response->assertForbidden();
  }

  public function test_create_child_page_creates_child_account(): void
  {
    $admin = $this->createAdmin();

    $parent = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(\App\Filament\Resources\Children\Pages\CreateChild::class)
      ->fillForm([
        "parent_id" => $parent->id,
        "first_name" => "John",
        "last_name" => "Kamau",
        "username" => "john",
        "xp" => 0,
        "level" => 1,
        "is_active" => true,
      ])
      ->call("create")
      ->assertHasNoFormErrors();

    $child = Child::where("username", "john")->first();

    $this->assertNotNull($child);

    $this->assertSame("John Kamau", $child->full_name);

    $this->assertSame($parent->id, $child->parent_id);

    $this->assertNotNull($child->user_id);

    $this->assertNotEmpty($child->pin);

    $this->assertFalse(Hash::check("1234", $child->pin));

    $this->assertTrue($child->user->hasRole("child"));
  }

  public function test_create_child_page_rejects_existing_username(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    $admin = $this->createAdmin();

    $parent = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(\App\Filament\Resources\Children\Pages\CreateChild::class)
      ->fillForm([
        "parent_id" => $parent->id,
        "first_name" => "John",
        "last_name" => "Otieno",
        "username" => "john",
        "xp" => 0,
        "level" => 1,
        "is_active" => true,
      ])
      ->call("create")
      ->assertHasFormErrors(["username"]);

    $this->assertSame(1, Child::where("username", "john")->count());
  }

  public function test_create_child_page_accepts_available_custom_username(): void
  {
    $admin = $this->createAdmin();

    $parent = User::factory()->create();

    $this->actingAs($admin);

    Livewire::test(\App\Filament\Resources\Children\Pages\CreateChild::class)
      ->fillForm([
        "parent_id" => $parent->id,
        "first_name" => "John",
        "last_name" => "Kamau",
        "username" => "john-kamau",
        "xp" => 0,
        "level" => 1,
        "is_active" => true,
      ])
      ->call("create")
      ->assertHasNoFormErrors();

    $this->assertDatabaseHas("children", [
      "username" => "john-kamau",
      "first_name" => "John",
      "last_name" => "Kamau",
      "parent_id" => $parent->id,
    ]);
  }
}
