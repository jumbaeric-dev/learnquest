<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildMobileNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_child_pages_render_the_mobile_navigation_toggle_and_drawer(): void
    {
        $user = User::factory()->create();

        Child::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('child.dashboard'));

        $response->assertOk()
            ->assertSee('lq-mobile-menu-trigger', false)
            ->assertSee('Toggle navigation menu')
            ->assertSee('aria-controls="child-navigation"', false)
            ->assertSee('id="child-navigation"', false)
            ->assertSee('My Universe')
            ->assertSee('Explorer');
    }

    public function test_guest_cannot_access_the_page_that_contains_mobile_navigation(): void
    {
        $this->get(route('child.dashboard'))
            ->assertRedirect(route('login'));
    }
}
