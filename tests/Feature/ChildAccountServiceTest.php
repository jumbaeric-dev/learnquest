<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Services\Child\ChildAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ChildAccountServiceTest extends TestCase
{
  use RefreshDatabase;

  protected function setUp(): void
  {
    parent::setUp();

    Role::findOrCreate("child", "web");
  }

  public function test_first_child_gets_base_username(): void
  {
    $service = app(ChildAccountService::class);

    $username = $service->suggestUsername("John");

    $this->assertSame("john", $username);
  }

  public function test_second_child_with_same_first_name_gets_numeric_suffix(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    $service = app(ChildAccountService::class);

    $username = $service->suggestUsername("John");

    $this->assertSame("john-2", $username);
  }

  public function test_third_child_with_same_first_name_gets_next_available_suffix(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    Child::factory()->create([
      "username" => "john-2",
    ]);

    $service = app(ChildAccountService::class);

    $username = $service->suggestUsername("John");

    $this->assertSame("john-3", $username);
  }

  public function test_username_suggestions_skip_existing_usernames(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    Child::factory()->create([
      "username" => "john-2",
    ]);

    Child::factory()->create([
      "username" => "john-4",
    ]);

    $service = app(ChildAccountService::class);

    $suggestions = $service->suggestUsernames("John", 3);

    $this->assertSame(["john-3", "john-5", "john-6"], $suggestions);
  }

  public function test_created_child_has_unique_username_and_hashed_pin(): void
  {
    $service = app(ChildAccountService::class);

    $parent = User::factory()->create();

    $child = $service->createChild([
      "first_name" => "John",
      "last_name" => "Kamau",
      "parent_id" => $parent->id,
      "pin" => "1234",
    ]);

    $this->assertNotEmpty($child->username);
    $this->assertNotEmpty($child->pin);

    $this->assertTrue(Hash::check("1234", $child->pin));

    $this->assertSame("john", $child->username);

    $this->assertTrue($child->user->hasRole("child"));
  }

  public function test_explicit_username_must_be_unique(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    $service = app(ChildAccountService::class);

    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage("The username 'john' is already taken.");

    $service->createChild([
      "first_name" => "John",
      "last_name" => "Kamau",
      "username" => "john",
      "parent_id" => \App\Models\User::factory()->create()->id,
    ]);
  }

  public function test_explicit_available_username_is_accepted(): void
  {
    $service = app(ChildAccountService::class);

    $parent = \App\Models\User::factory()->create();

    $child = $service->createChild([
      "first_name" => "John",
      "last_name" => "Kamau",
      "username" => "john-kamau",
      "parent_id" => $parent->id,
    ]);

    $this->assertSame("john-kamau", $child->username);
  }

  public function test_username_suggestions_can_be_used_after_collision(): void
  {
    Child::factory()->create([
      "username" => "john",
    ]);

    Child::factory()->create([
      "username" => "john-2",
    ]);

    Child::factory()->create([
      "username" => "john-3",
    ]);

    $service = app(ChildAccountService::class);

    $suggestions = $service->suggestUsernames("john", 3);

    $this->assertSame(["john-4", "john-5", "john-6"], $suggestions);
  }
}
