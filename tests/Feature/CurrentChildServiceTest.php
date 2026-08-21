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
}
