<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_child_can_view_their_profile(): void
    {
        $user = User::factory()->create();

        $child = Child::factory()->create([
            'user_id' => $user->id,
            'parent_id' => $user->id,
            'first_name' => 'Emma',
            'last_name' => 'Explorer',
            'username' => 'emma_explorer',
            'level' => 3,
            'xp' => 250,
        ]);

        $this->actingAs($user)
            ->get(route('child.profile'))
            ->assertOk()
            ->assertSee('My Explorer Profile')
            ->assertSee('Emma Explorer')
            ->assertSee('@emma_explorer')
            ->assertSee('Level 3 Explorer')
            ->assertSee('250');
    }

    public function test_authenticated_user_without_a_child_is_forbidden(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('child.profile'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('child.profile'))
            ->assertRedirect(route('login'));
    }
}
