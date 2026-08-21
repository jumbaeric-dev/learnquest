<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildContextSurfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_child_can_access_explore(): void
    {
        $user = User::factory()->create();

        Child::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/child/explore');

        $response->assertOk();
    }

    public function test_authenticated_child_can_access_missions(): void
    {
        $user = User::factory()->create();

        Child::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->actingAs($user);

        $response = $this->get('/child/missions');

        $response->assertOk();
    }

    public function test_authenticated_user_without_child_cannot_access_explore(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/child/explore');

        $response->assertForbidden();
    }

    public function test_authenticated_user_without_child_cannot_access_missions(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/child/missions');

        $response->assertForbidden();
    }
}