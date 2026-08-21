<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChildDashboardTest extends TestCase
{
  use RefreshDatabase;

  public function test_authenticated_child_can_access_dashboard(): void
  {
    $user = User::factory()->create();

    $child = Child::factory()->create([
      "user_id" => $user->id,
      "first_name" => "Eric",
    ]);

    $this->actingAs($user);

    $response = $this->get("/child/my-universe");

    $response->assertOk();
    $response->assertSee("Eric");
  }

  public function test_guest_cannot_access_child_dashboard(): void
  {
    $response = $this->get("/child/my-universe");

    $response->assertRedirect("/login");
  }

  public function test_child_dashboard_uses_authenticated_child_context(): void
  {
    $firstUser = User::factory()->create();
    $secondUser = User::factory()->create();

    $firstChild = Child::factory()->create([
      "user_id" => $firstUser->id,
      "first_name" => "FirstExplorer",
      "xp" => 100,
    ]);

    Child::factory()->create([
      "user_id" => $secondUser->id,
      "first_name" => "SecondExplorer",
      "xp" => 900,
    ]);

    $this->actingAs($firstUser);

    $response = $this->get("/child/my-universe");

    $response->assertOk();
    $response->assertSee("FirstExplorer");
    $response->assertDontSee("SecondExplorer");
  }
}
