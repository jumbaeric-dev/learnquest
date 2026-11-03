<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Services\Child\Context\CurrentChildService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentChildServiceTest extends TestCase
{
  use RefreshDatabase;

  public function test_guest_has_no_current_child(): void
  {
    $current = app(CurrentChildService::class)->current();

    $this->assertNull($current);
  }

  public function test_authenticated_child_resolves_own_child(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
    ]);

    $otherChild = Child::factory()->create();

    $this->actingAs($user);

    $current = app(CurrentChildService::class)->current();

    $this->assertNotNull($current);
    $this->assertSame($child->id, $current->id);
    $this->assertNotSame($otherChild->id, $current->id);
  }

  public function test_authenticated_user_without_child_has_no_current_child(): void
  {
    $user = User::factory()->create();

    $this->actingAs($user);

    $current = app(CurrentChildService::class)->current();

    $this->assertNull($current);
  }

  public function test_authenticated_child_cannot_resolve_another_users_child(): void
  {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $firstChild = Child::factory()->create([
      "user_id" => $firstUser->id,
    ]);

    Child::factory()->create([
      "user_id" => $secondUser->id,
    ]);

    $this->actingAs($firstUser);

    $current = app(CurrentChildService::class)->current();

    $this->assertNotNull($current);
    $this->assertSame($firstChild->id, $current->id);
  }
  public function test_parent_resolves_only_the_child_selected_from_their_own_family(): void
  {
    \Spatie\Permission\Models\Role::findOrCreate("parent", "web");
    $parent = User::factory()->create();
    $parent->assignRole("parent");
    $ownChild = Child::factory()->create(["parent_id" => $parent->id, "is_active" => true]);
    $otherParent = User::factory()->create();
    $otherChild = Child::factory()->create(["parent_id" => $otherParent->id, "is_active" => true]);

    $this->actingAs($parent);
    session(["active_child_id" => $ownChild->id]);

    $this->assertSame($ownChild->id, app(CurrentChildService::class)->current()?->id);

    session(["active_child_id" => $otherChild->id]);
    $this->assertNull(app(CurrentChildService::class)->current());
  }

  public function test_parent_cannot_resolve_an_inactive_selected_child(): void
  {
    \Spatie\Permission\Models\Role::findOrCreate("parent", "web");
    $parent = User::factory()->create();
    $parent->assignRole("parent");
    $child = Child::factory()->create(["parent_id" => $parent->id, "is_active" => false]);

    $this->actingAs($parent);
    session(["active_child_id" => $child->id]);

    $this->assertNull(app(CurrentChildService::class)->current());
  }

}
